<?php

namespace FatchipAfterbuy\Services\WriteData\Internal;

use Doctrine\ORM\OptimisticLockException;
use FatchipAfterbuy\Components\Helper;
use FatchipAfterbuy\Models\Status;
use FatchipAfterbuy\Services\Helper\ShopwareArticleHelper;
use FatchipAfterbuy\Services\WriteData\AbstractWriteDataService;
use FatchipAfterbuy\Services\WriteData\WriteDataInterface;
use FatchipAfterbuy\ValueObjects\Article as ValueArticle;
use FatchipAfterbuy\ValueObjects\ProductPicture;
use Shopware\Components\Model\ModelRepository;
use Shopware\Models\Article\Article as ShopwareArticle;
use Shopware\Models\Article\Configurator\Group as ConfiguratorGroup;
use Shopware\Models\Article\Configurator\Option as ConfiguratorOption;
use Shopware\Models\Article\Detail as ArticleDetail;
use Shopware\Models\Article\Image as ArticleImage;
use Shopware\Models\Article\Image\Mapping as ImageMapping;
use Shopware\Models\Article\Image\Rule as ImageRule;
use Shopware\Models\Attribute\Article as ArticlesAttribute;
use Shopware\Models\Customer\Group as CustomerGroup;
use Shopware\Models\Media\Media;


class WriteProductsService extends AbstractWriteDataService implements WriteDataInterface
{

    /**
     * @param array $data
     */
    public function put(array $data)
    {
        $this->transform($data);
        $this->send($data);
    }

    /**
     * transforms valueObject into final structure for storage
     * could may be moved into separate helper
     *
     * @param ValueArticle[] $valueArticles
     * @throws OptimisticLockException
     */
    public function transform(array $valueArticles)
    {
        /**
         * @var CustomerGroup $customerGroup
         */
        $customerGroup = $this->entityManager->getRepository(CustomerGroup::class)->findOneBy(
            array('id' => $this->config['customerGroup'])
        );
        $netInput = $customerGroup->getTaxInput();

        if ( ! $customerGroup) {
            $this->logger->error('Target customer group not set', array('Import', 'Articles'));

            return;
        }

        /**
         * @var ShopwareArticleHelper $helper
         */
        $helper = $this->helper;

        foreach ($valueArticles as $valueArticle) {

            /**
             * @var ShopwareArticle $shopwareArticle
             */
            try {
                $shopwareArticle = $helper->getMainArticle(
                    $valueArticle->getExternalIdentifier(),
                    $valueArticle->getName(),
                    $valueArticle->getMainArticleId()
                );
            } catch (OptimisticLockException $e) {
                // TODO: correct error handling. This is NOT the correct place to handle such kind of errors. This should be done directly where the flush takes place
            }

            if ( ! $shopwareArticle) {
                continue;
            }

            /**
             * @var ArticleDetail $articleDetail
             */
            $articleDetail = $helper->getDetail($valueArticle->getExternalIdentifier(), $shopwareArticle);

            //set main values
            $articleDetail->setLastStock($valueArticle->getStockMin());
            $shopwareArticle->setName($valueArticle->getName());
            $shopwareArticle->setDescriptionLong($valueArticle->getDescription());
            $articleDetail->setInStock($valueArticle->getStock());
            $articleDetail->setEan($valueArticle->getEan());

            if ($valueArticle->isActive()) {
                $articleDetail->setActive(1);
                $shopwareArticle->setActive(true);
            }

            $price = Helper::convertPrice($valueArticle->getPrice(), $valueArticle->getTax(), false, $netInput);


            $helper->storePrices($articleDetail, $customerGroup, $price);

            $shopwareArticle->setSupplier($helper->getSupplier($valueArticle->getManufacturer()));

            $helper->getArticleAttributes($shopwareArticle, $articleDetail, $valueArticle->getMainArticleId());

            $shopwareArticle->setTax($helper->getTax($valueArticle->getTax()));

            $helper->assignVariants($shopwareArticle, $articleDetail, $valueArticle->variants);

            $this->entityManager->persist($shopwareArticle);

            //have to flush cuz parent is not getting found otherwise
            try {
                $this->entityManager->flush();
            } catch (OptimisticLockException $e) {
            }
        }

        foreach ($valueArticles as $valueArticle) {

            $mainArticleId = $valueArticle->getMainArticleId() ?: $valueArticle->getExternalIdentifier();

            /** @var ArticlesAttribute $attribute */
            $attribute = $this->entityManager->getRepository(ArticlesAttribute::class)->findOneBy(
                ['afterbuyParentId' => $mainArticleId]
            );

            if ( ! $attribute) {
                // no attribute with given mainArticleId
                continue;
            }

            $mainDetail = $attribute->getArticle()->getMainDetail();

            foreach ($valueArticle->getProductPictures() as $productPicture) {

                $media = $helper->createMediaImage(
                    $productPicture->getUrl(),
                    'Artikel'
                );

                if(is_null($media)) {
                    continue;
                }

                /** @var ArticleDetail $articleDetail */
                $articleDetail = $this->entityManager->getRepository(ArticleDetail::class)->findOneBy(
                    ['number' => $valueArticle->getExternalIdentifier()]
                );

                /** @var ModelRepository $imageRepo */
                $imageRepo = $this->entityManager->getRepository(ArticleImage::class);

                // all images, assigned to current article with current media
                /** @var ArticleImage[] $images */
                $images = $imageRepo->findBy([
                    'mediaId'   => $media->getId(),
                    'articleId' => $mainDetail->getArticleId(),
                ]);

                if (count($images) === 0) {
                    $image = $this->createParentImage($media, $productPicture, $mainDetail->getArticle());
                } else {
                    $image = $images[0];
                }

                $mapping = new ImageMapping();
                $mapping->setImage($image);

                if (is_array($valueArticle->variants) && count($valueArticle->variants) > 0) {
                    foreach ($valueArticle->variants as $variantOption) {
                        $optionName = $variantOption['value'];
                        $optionGroup = $variantOption['option'];

                        $group = $this->entityManager->getRepository(ConfiguratorGroup::class)->findOneBy([
                            'name'  => $optionGroup,
                        ]);

                        /** @var ConfiguratorOption $option */
                        $option = $this->entityManager->getRepository(ConfiguratorOption::class)->findOneBy([
                            'name'  => $optionName,
                            'group' => $group,
                        ]);

                        $rule = new ImageRule();
                        $rule->setMapping($mapping);
                        $rule->setOption($option);

                        $mapping->getRules()->add($rule);
                    }

                    $image->getMappings()->add($mapping);
                }

                if ( ! $valueArticle->isMainProduct()) {
                    $this->createChildImage($image, $articleDetail);
                }

                // reset preview image status
                if ($valueArticle->isMainProduct() && $productPicture->getNr() === '0' && $image->getMain() !== 1) {
                    foreach ($mainDetail->getArticle()->getImages() as $_image) {
                        $_image->setMain(2);
                    }
                    $image->setMain(1);
                }
            }
        }
    }


    /**
     * @param $targetData
     */
    public function send($targetData)
    {
        // TODO: necessary? We flush already earlier
        try {
            $this->entityManager->flush();
        } catch (OptimisticLockException $e) {
            // TODO: handle error
        }

        $this->storeSubmissionDate('lastProductImport');
        $this->helper->setArticlesWithoutAnyActiveVariantToInactive();
    }

    /**
     * @param Media           $media
     * @param ProductPicture  $productPicture
     * @param ShopwareArticle $article
     *
     * @return ArticleImage
     */
    public function createParentImage(
        Media $media,
        ProductPicture $productPicture,
        ShopwareArticle $article
    ): ArticleImage {
        $image = new ArticleImage();

        $image->setArticle($article);
        $image->setPath($media->getName());
        $image->setDescription($media->getDescription());
        $image->setPosition($productPicture->getNr());
        $image->setExtension($media->getExtension());
        $image->setMedia($media);

        $this->entityManager->persist($image);

        try {
            $this->entityManager->flush();
        } catch (OptimisticLockException $e) {
        }

        return $image;
    }

    /**
     * @param ArticleImage  $parent
     * @param ArticleDetail $detail
     *
     * @return ArticleImage
     */
    public function createChildImage(ArticleImage $parent, ArticleDetail $detail): ArticleImage
    {
        $image = new ArticleImage();

        $image->setPosition($parent->getPosition());
        $image->setExtension($parent->getExtension());
        $image->setParent($parent);
        $image->setArticleDetail($detail);

        $this->entityManager->persist($image);

        try {
            $this->entityManager->flush();
        } catch (OptimisticLockException $e) {
        }

        return $image;
    }

    public function getArticleImportDateFilter($force = false) {
        if($force) {
            return array();
        }

        /**
         * @var $lastDate Status
         */
        $lastDate = $this->entityManager->getRepository("FatchipAfterbuy\Models\Status")->find(1);

        if(!$lastDate) {
            return array();
        }

        if(!$lastDate->getLastProductImport()) {
            return array();
        }

        $filterDate = date_format($lastDate->getLastProductImport(), 'd.m.Y H:i:s');

        $filter = array(
            'Filter' => array(
                'FilterName' => 'DateFilter',
                'FilterValues' => array(
                    'DateFrom' => $filterDate,
                    'FilterValue' => 'ModDate'
                )
            )
        );

        return $filter;
    }
}

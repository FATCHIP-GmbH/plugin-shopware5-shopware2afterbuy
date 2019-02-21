<?php

namespace abaccAfterbuy\Services\WriteData\Internal;

use Doctrine\ORM\OptimisticLockException;
use abaccAfterbuy\Components\Helper;
use abaccAfterbuy\Models\Status;
use abaccAfterbuy\Services\Helper\ShopwareArticleHelper;
use abaccAfterbuy\Services\WriteData\AbstractWriteDataService;
use abaccAfterbuy\Services\WriteData\WriteDataInterface;
use abaccAfterbuy\ValueObjects\Article as ValueArticle;
use abaccAfterbuy\ValueObjects\ProductPicture;
use Shopware\Components\Model\ModelRepository;
use Shopware\Models\Article\Article as ShopwareArticle;
use Shopware\Models\Article\Configurator\Group as ConfiguratorGroup;
use Shopware\Models\Article\Configurator\Option as ConfiguratorOption;
use Shopware\Models\Article\Detail as ArticleDetail;
use Shopware\Models\Article\Detail;
use Shopware\Models\Article\Image as ArticleImage;
use Shopware\Models\Article\Image\Mapping as ImageMapping;
use Shopware\Models\Article\Image\Rule as ImageRule;
use Shopware\Models\Attribute\Article as ArticlesAttribute;
use Shopware\Models\Attribute\Article;
use Shopware\Models\Attribute\Category as CategoryAttribute;
use Shopware\Models\Customer\Group as CustomerGroup;
use Shopware\Models\Media\Media;
use Zend_Db_Adapter_Exception;


class WriteProductsService extends AbstractWriteDataService implements WriteDataInterface
{

    /** @var ShopwareArticleHelper $helper */
    public $helper;

    /**
     * @param array $data
     */
    public function put(array $data)
    {
        $this->transform($data);
        return $this->send($data);
    }

    /**
     * transforms valueObject into final structure for storage
     * could may be moved into separate helper
     *
     * @param ValueArticle[] $valueArticles
     */
    public function transform(array $valueArticles)
    {
        $this->logger->debug('Importing articles', $valueArticles);

        /**
         * @var CustomerGroup $customerGroup
         */
        $customerGroup = $this->entityManager->getRepository(CustomerGroup::class)->findOneBy(
            array('id' => $this->config['customerGroup'])
        );

        if ( ! $customerGroup) {
            $this->logger->error('Target customer group not set', array('Import', 'Articles'));

            exit('Target customer group not set');
        }

        $netInput = $customerGroup->getTaxInput();

        foreach ($valueArticles as $valueArticle) {

            /**
             * @var ShopwareArticle $shopwareArticle
             */
            try {
                $shopwareArticle = $this->helper->getMainArticle(
                    $valueArticle->getExternalIdentifier(),
                    $valueArticle->getName(),
                    $valueArticle->getMainArticleId()
                );
            } catch (OptimisticLockException $e) {
                $this->logger->error('Error creating article', array($valueArticle));
                continue;
            }

            if ( ! $shopwareArticle) {
                continue;
            }

            /**
             * @var ArticleDetail $articleDetail
             */
            $articleDetail = $this->helper->getDetail($valueArticle->getExternalIdentifier(), $shopwareArticle);

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


            $this->helper->storePrices($articleDetail, $customerGroup, $price);

            $shopwareArticle->setSupplier($this->helper->getSupplier($valueArticle->getManufacturer()));

            $this->helper->getArticleAttributes($shopwareArticle, $articleDetail, $valueArticle->getMainArticleId());

            $shopwareArticle->setTax($this->helper->getTax($valueArticle->getTax()));

            $this->helper->assignVariants($shopwareArticle, $articleDetail, $valueArticle->variants);

            $this->entityManager->persist($shopwareArticle);

            //have to flush cuz parent is not getting found otherwise
            try {
                $this->entityManager->flush();
            } catch (OptimisticLockException $e) {
            }
        }

        //Category Association
        //TODO: refactor
        foreach ($valueArticles as $valueArticle) {
            if ( ! $valueArticle->isMainProduct()) {
                continue;
            }

            foreach ($valueArticle->getExternalCategoryIds() as $categoryId) {
                /** @var CategoryAttribute $categoryAttribute */
                $categoryAttribute = $this->entityManager->getRepository(CategoryAttribute::class)->findOneBy(
                    ['afterbuyCatalogId' => $categoryId]
                );

                if($categoryAttribute === null) {
                    continue;
                }

                $category = $categoryAttribute->getCategory();

                $mainArticleId = $valueArticle->getMainArticleId() ?: $valueArticle->getExternalIdentifier();

                /** @var ArticlesAttribute $articleAttribute */
                $articleDetail = $this->entityManager->getRepository(Detail::class)->findOneBy(
                    ['number' => $mainArticleId]
                );

                if($articleDetail === null) {
                    $articleDetail = $this->entityManager->getRepository(Article::class)->findOneBy(
                        ['afterbuyParentId' => $mainArticleId]
                    );
                }

                if($articleDetail && $article = $articleDetail->getArticle()) {
                    $article->addCategory($category);
                }
            }
        }

        //Image Association
        //TODO: refactor
        $mappings = [];

        foreach ($valueArticles as $valueArticle) {

            $mainArticleId = $valueArticle->getMainArticleId() ?: $valueArticle->getExternalIdentifier();

            /** @var ArticlesAttribute $attribute */
            $attribute = $this->entityManager->getRepository(ArticlesAttribute::class)->findOneBy(
                ['afterbuyParentId' => $mainArticleId]
            );

            if ( ! $attribute) {
                $mainDetail = $this->entityManager->getRepository(Detail::class)->findOneBy(
                    ['number' => $mainArticleId]
                );
            }
            else {
                $mainDetail = $attribute->getArticle()->getMainDetail();
            }


            foreach ($valueArticle->getProductPictures() as $productPicture) {

                $media = $this->helper->createMediaImage(
                    $productPicture->getUrl(),
                    'Artikel'
                );

                if ($media === null) {
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

                if($articleDetail && $articleDetail->getArticle()) {
                    $images = $imageRepo->findBy([
                        'mediaId' => $media->getId(),
                        'articleId' => $articleDetail->getArticle()->getId(),
                    ]);
                }
                elseif($mainDetail && $mainDetail->getArticleId()) {
                    $images = $imageRepo->findBy([
                        'mediaId' => $media->getId(),
                        'articleId' => $mainDetail->getArticleId(),
                    ]);
                }
                else {
                    $images = array();
                }

                if (count($images) === 0) {
                    $image = $this->createParentImage($media, $productPicture, $mainDetail->getArticle());
                } else {
                    $image = $images[0];
                }

                $mapping = null;

                if($image->getId()) {
                    //get mapping from cache
                    if(array_key_exists($image->getId(), $mappings)) {
                        $mapping = $mappings[$image->getId()];
                    }

                    if(!$mapping) {
                        $mapping = $this->entityManager->createQueryBuilder()
                            ->select(['mapping'])
                            ->from(ArticleImage\Mapping::class, 'mapping')
                            ->where('mapping.imageId = :image')
                            ->setParameters(array('image' => $image->getId()))
                            ->setMaxResults(1)
                            ->getQuery()->getOneOrNullResult();
                    }
                }

                if(!$mapping) {
                    $mapping = new ImageMapping();
                    $mapping->setImage($image);
                }

                //we have to cache the mappings, otherwise we will not be able to find them if unflushed
                if($image->getId()) {
                    $mappings[$image->getId()] = $mapping;
                }

                if (is_array($valueArticle->variants) && count($valueArticle->variants) > 0) {
                    foreach ($valueArticle->variants as $variantOption) {
                        $optionName = $variantOption['value'];
                        $optionGroup = $variantOption['option'];

                        $group = $this->entityManager->getRepository(ConfiguratorGroup::class)->findOneBy([
                            'name' => $optionGroup,
                        ]);

                        /** @var ConfiguratorOption $option */
                        $option = $this->entityManager->getRepository(ConfiguratorOption::class)->findOneBy([
                            'name'  => $optionName,
                            'group' => $group,
                        ]);

                        $rule = null;

                        if($mapping->getId()) {
                            $rule = $this->entityManager->createQueryBuilder()
                                ->select(['rule'])
                                ->from(ArticleImage\Rule::class, 'rule')
                                ->where('rule.mappingId = :mapping')
                                ->andWhere('rule.optionId = :option')
                                ->setParameters(array('mapping' => $mapping->getId(), 'option' => $option->getId()))
                                ->setMaxResults(1)
                                ->getQuery()->getOneOrNullResult();
                        }

                        if(!$rule) {
                            $rule = new ImageRule();
                            $rule->setMapping($mapping);
                            $rule->setOption($option);
                            $mapping->getRules()->add($rule);
                        }
                    }

                    if(!$image->getMappings()->count()) {
                        $image->getMappings()->add($mapping);
                    }
                    else {
                        $this->entityManager->persist($mapping);
                    }
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
     * @return
     */
    public function send($targetData)
    {
        try {
            $this->entityManager->flush();
        } catch (OptimisticLockException $e) {
            $this->logger->error('Error storing products', $targetData);
            exit('Error storing products');
        }

        if(!empty($targetData)) {
            $this->storeSubmissionDate('lastProductImport');
            $this->helper->setArticlesWithoutAnyActiveVariantToInactive();
        }

        return $targetData;
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
    ) {
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
    public function createChildImage(ArticleImage $parent, ArticleDetail $detail)
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

    public function getArticleImportDateFilter($force = false)
    {
        if ($force) {
            return array();
        }

        /**
         * @var $lastDate Status
         */
        $lastDate = $this->entityManager->getRepository(Status::class)->find(1);

        if ( ! $lastDate) {
            return array();
        }

        if ( ! $lastDate->getLastProductImport()) {
            return array();
        }

        $filterDate = date_format($lastDate->getLastProductImport(), 'd.m.Y H:i:s');

        $filter = array(
            'Filter' => array(
                'FilterName'   => 'DateFilter',
                'FilterValues' => array(
                    'DateFrom'    => $filterDate,
                    'FilterValue' => 'ModDate',
                ),
            ),
        );

        return $filter;
    }
}

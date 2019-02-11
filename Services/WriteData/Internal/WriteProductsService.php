<?php

namespace FatchipAfterbuy\Services\WriteData\Internal;

use Doctrine\ORM\NonUniqueResultException;
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
use Shopware\Models\Attribute\Category as CategoryAttribute;
use Shopware\Models\Customer\Group as CustomerGroup;
use Shopware\Models\Media\Media;


class WriteProductsService extends AbstractWriteDataService implements WriteDataInterface
{

    /** @var ShopwareArticleHelper $helper */
    public $helper;

    /**
     * @param array $data
     *
     * @return array
     */
    public function put(array $data): array
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

        /** @var CustomerGroup $customerGroup */
        $customerGroup = $this->entityManager->getRepository(CustomerGroup::class)->findOneBy(
            array('id' => $this->config['customerGroup'])
        );
        $netInput = $customerGroup->getTaxInput();

        if ( ! $customerGroup) {
            $this->logger->error('Target customer group not set', array('Import', 'Articles'));

            return;
        }

        $this->helper->importArticle($valueArticles, $netInput, $customerGroup);

        // Category Association
        $this->helper->associateCategories($valueArticles);

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
                $mainDetail = $this->entityManager->getRepository(ArticleDetail::class)->findOneBy(
                    ['number' => $mainArticleId]
                );
            } else {
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

                if ($articleDetail && $articleDetail->getArticle()) {
                    $images = $imageRepo->findBy([
                        'mediaId'   => $media->getId(),
                        'articleId' => $articleDetail->getArticle()->getId(),
                    ]);
                } elseif ($mainDetail && $mainDetail->getArticleId()) {
                    $images = $imageRepo->findBy([
                        'mediaId'   => $media->getId(),
                        'articleId' => $mainDetail->getArticleId(),
                    ]);
                } else {
                    $images = array();
                }

                if (count($images) === 0) {
                    $image = $this->createParentImage($media, $productPicture, $mainDetail->getArticle());
                } else {
                    $image = $images[0];
                }

                $mapping = null;

                if ($image->getId()) {
                    //get mapping from cache
                    if (array_key_exists($image->getId(), $mappings)) {
                        $mapping = $mappings[$image->getId()];
                    }

                    if ( ! $mapping) {
                        $query = $this->entityManager->createQueryBuilder()
                            ->select(['mapping'])
                            ->from(ImageMapping::class, 'mapping')
                            ->where('mapping.imageId = :image')
                            ->setParameters(array('image' => $image->getId()))
                            ->setMaxResults(1)
                            ->getQuery();

                        try {
                            $mapping = $query->getOneOrNullResult();
                        } catch (NonUniqueResultException $e) {
                            // TODO: handle exception
                        }
                    }
                }

                if ( ! $mapping) {
                    $mapping = new ImageMapping();
                    $mapping->setImage($image);
                }

                //we have to cache the mappings, otherwise we will not be able to find them if not flushed
                if ($image->getId()) {
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

                        if ($mapping->getId()) {
                            $query = $this->entityManager->createQueryBuilder()
                                ->select(['rule'])
                                ->from(ImageRule::class, 'rule')
                                ->where('rule.mappingId = :mapping')
                                ->andWhere('rule.optionId = :option')
                                ->setParameters(array('mapping' => $mapping->getId(), 'option' => $option->getId()))
                                ->setMaxResults(1)
                                ->getQuery();

                            try {
                                $rule = $query->getOneOrNullResult();
                            } catch (NonUniqueResultException $e) {
                                // TODO: handle exception
                            }
                        }

                        if ( ! $rule) {
                            $rule = new ImageRule();
                            $rule->setMapping($mapping);
                            $rule->setOption($option);
                            $mapping->getRules()->add($rule);
                        }
                    }

                    if ( ! $image->getMappings()->count()) {
                        $image->getMappings()->add($mapping);
                    } else {
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
     * @param array $targetData
     *
     * @return array
     */
    public function send($targetData): array
    {
        try {
            $this->entityManager->flush();
        } catch (OptimisticLockException $e) {
            $this->logger->error('Error storing products', $targetData);
        }

        $this->storeSubmissionDate('lastProductImport');
        $this->helper->setArticlesWithoutAnyActiveVariantToInactive();

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

    public function getArticleImportDateFilter($force = false)
    {
        if ($force) {
            return array();
        }

        /** @var $lastDate Status */
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

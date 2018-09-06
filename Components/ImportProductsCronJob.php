<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 16.07.18
 * Time: 10:19
 */

namespace Shopware\FatchipShopware2Afterbuy\Components;

use Exception;

use Doctrine\ORM\OptimisticLockException;

use Shopware\Components\Api\Exception\CustomValidationException;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Exception\ParameterMissingException;
use Shopware\Components\Api\Exception\ValidationException;
use Shopware\Components\Api\Resource\Article as ArticleResource;
use Shopware\Components\Api\Resource\Category as CategoryResource;
use Shopware\Components\Api\Resource\Variant as VariantResource;
use Shopware\Components\Api\Manager as ApiManager;

use Shopware\CustomModels\FatchipShopware2Afterbuy\PluginConfig;

use Shopware\Models\Article\Article;
use Shopware\Models\Article\Detail as ArticleDetail;
use Shopware\Models\Category\Category;
use Shopware\Models\Tax\Repository;
use Shopware\Models\Tax\Tax;

use Fatchip\Afterbuy\ApiClient;


/**
 * Import products from AfterBuy API and import them into shopware
 *
 * @package Shopware\FatchipShopware2Afterbuy\Components
 */
class ImportProductsCronJob {
    /** @var PluginConfig pluginConfig */
    protected $pluginConfig;

    /** @var JSONCache */
    protected $caching;


    /**
     * ImportProductsCronJob constructor.
     */
    public function __construct() {
        $this->pluginConfig = Shopware()
            ->Models()
            ->getRepository(
                'Shopware\CustomModels\FatchipShopware2Afterbuy\PluginConfig'
            )
            ->findOneBy(['id' => '1']);

        $this->caching = new JSONCache(
            $this->pluginConfig->getAfterbuyPartnerId()
        );
    }


    // TODO: remove in productive
    public function callProducts() {
        return $this->retrieveProductsArray();
    }

    // TODO: remove in productive
    public function callCatalogs() {
        return $this->retrieveCatalogsArray();
    }

    /**
     * The entry point of this Class.
     */
    public function importProducts2Shopware() {
        $this->importCatalogs();
        $productIds = $this->importProducts();
    }

    protected function importCatalogs() {
        $pageNumber = 0;

        $this->caching->deleteCache('catalogs');

        do {
            $catalogsResult = $this->retrieveCatalogsArray(
                200,
                2,
                $pageNumber++
            );

            $catalogs = $catalogsResult['Result']['Catalogs']['Catalog'];

            foreach ($catalogs as $catalog) {
                $catalog = [
                    $catalog['CatalogID'] => $catalog,
                ];

                $this->caching->cacheData($catalog, 'catalogs');
            }
        } while ($catalogsResult['Result']['HasMoreCatalogs']);
    }

    protected function importProducts() {
        /** @var int[] $productIds */
        $pageIndex = 0;

        $variantSetParents = [];
        $variantSetChildren = [];
        $singleProducts = [];

        $this->caching->deleteCache('products');

        // pull products from AB and cache to files
        do {
            $productsResult = $this->retrieveProductsArray(250, $pageIndex++);

            $products = $productsResult['Result']['Products']['Product'];

            foreach ($products as $product) {
                $productID = $product['ProductID'];

                // set related?
                if (isset($product['BaseProductFlag'])) {
                    // variantSet parent?
                    switch ($product['BaseProductFlag']) {
                        case 1:
                            // variationSet parent
                            $variantSetParents[$productID] = $product;
                            break;
                        case 2:
                            // productSet parent
                            // TODO: ProductSet
                            break;
                        case 3:
                            // set children
                            $variantSetChildren[$productID] = $product;
                            break;
                        default:
                            // TODO: error handling on wrong BaseProductFlag
                    }
                } else {
                    $singleProducts[$productID] = $product;
                }

                $product = [
                    $productID => $product,
                ];
                $this->caching->cacheData($product, 'products');
            }
        } while ($productsResult['Result']['HasMoreProducts']);

        return [
            'variantSetParents'  => $variantSetParents,
            'variantSetChildren' => $variantSetChildren,
            'singleProducts'     => $singleProducts,
        ];

        // $strategy = $this->pluginConfig->getMissingProductsStrategy();
        //
        // $importArticles = $converter->convertProducts2Articles(
        //     $products,
        //     $categoryId
        // );
        //
        // $productIds = array_merge(
        //     $productIds,
        //     $this->writeArticles($importArticles)
        // );
        //

        // if ($strategy === 'delete') {
        //     $this->deleteSurplus($productIds);
        // }
    }

    protected function retrieveCatalogsArray(
        $maxCatalogs = 200,
        $detailLevel = 2,
        $pageNumber = 0
    ) {
        /** @var ApiClient $apiClient */
        $apiClient = Shopware()->Container()->get('afterbuy_api_client');
        $catalogs = $apiClient->getCatalogsFromAfterbuy(
            $maxCatalogs,
            $detailLevel,
            $pageNumber
        );

        return $catalogs;
    }

    /**
     * Call the AfterBuy API and retrieve all the products as array.
     *
     * @param int    $iMaxShopItems
     * @param int    $iPage
     * @param string $timestamp
     *
     * @return array
     */
    protected function retrieveProductsArray(
        $iMaxShopItems = 250,
        $iPage = 0,
        $timestamp = ''
    ) {
        // Get SDK object
        /** @var ApiClient $apiClient */
        $apiClient = Shopware()->Container()->get('afterbuy_api_client');
        // $apiClient = new ApiMock();

        $dataFilter = [
            [
                'FilterName'   => 'DateFilter',
                'FilterValues' => [
                    'DateFrom'    => date('d.m.Y H:i:s', $timestamp),
                    'FilterValue' => 'ModDate',
                ],
            ],
        ];

        // Get all products from AfterbuyAPI
        $productsResult = $apiClient->getShopProductsFromAfterbuy(
            $iMaxShopItems,
            $iPage,
            $dataFilter
        );
        if ($productsResult['CallStatus'] != 'Success') {
            // TODO: fix error handling
            die('GetShopProducts: CallStatus: ['
                . $productsResult['CallStatus']
                . '] Awaited: [Success].');
        }

        return $productsResult;
    }

    protected function importImages() {
        /** @var int[] $productIds */
        $pageIndex = 0;

        do {
            $productsResult = $this->retrieveProductsArray(250, $pageIndex++);

            $products = $productsResult['Result']['Products']['Product'];

            $imageCrawler = new ImageCrawler();

            $imageArray = $imageCrawler->retrieveImages($products);

            foreach ($imageArray as $productId => $images) {
                $query = '
                SELECT a.`id` articleID,  at.`afterbuy_productid`
                FROM `s_articles` AS a
                INNER JOIN `s_articles_details` AS d ON a.`main_detail_id` = d.`id`
                INNER JOIN `s_articles_attributes` AS at ON d.`id` = at.`articledetailsID`
                WHERE `afterbuy_productid` = ?
            ';
                $articleId = Shopware()->Db()->fetchOne($query, $productId);

                // retrieve SW article name from ProductID
                /** @var ArticleResource $articleResource */
                $articleResource = ApiManager::getResource('article');

                /** @var Article $article */
                $article = $articleResource->getRepository()->find($articleId);

                // create array
                $createImages = [
                    // 'name'   => $article->getName(),
                    'images' => [],
                ];

                foreach ($images as $image) {
                    $options = [];
                    foreach ($image['configurations'] as $value) {
                        $optionsIndex = count($options);
                        $options[] = [];

                        foreach ($value as $v) {
                            $options[$optionsIndex][] = ['name' => $v];
                        }
                    }
                    $createImages['images'][] = [
                        'link'    => $image['link'],
                        'options' => $options,
                    ];
                }

                // push array to api
                try {
                    $this->updateArticle($articleId, $createImages);
                } catch (Exception $e) {
                    echo $e;
                    echo '<br>';
                }
            }

        } while ($productsResult['Result']['HasMoreProducts']);
    }

    protected function createCategory() {
        /** @var CategoryResource $categoryResource */
        $categoryResource = ApiManager::getResource('category');
        $categoryName = $this->pluginConfig->getCategory();

        if ( ! $categoryName) {
            return null;
        }

        /** @var Category $category */
        $category = $categoryResource->getRepository()->findOneBy(
            ['name' => $categoryName]
        );

        if ( ! $category) {
            $newCategory = [
                'parentId' => 3,
                'name'     => $categoryName,
            ];

            try {
                $categoryResource->create($newCategory);
            } catch (ValidationException $e) {
                // TODO: handle  exception
            } catch (Exception $e) {
                // TODO: handle  exception
            }
        }

        $category = $categoryResource->getRepository()->findOneBy(
            ['name' => $categoryName]
        );

        return $category->getId();
    }

    /**
     * Imports the given articles array into shopware and returns an array with
     * all imported productId's.
     *
     * @param array $articles
     *
     * @return array
     */
    protected function writeArticles($articles) {
        $detailRepository = Shopware()->Models()->getRepository(
            'Shopware\Models\Article\Detail'
        );

        $productIds = [];

        foreach ($articles as $articleArray) {
            $productIds[] =
                $articleArray['mainDetail']['attribute']['afterbuyProductid'];

            $this->createTax($articleArray['tax']);

            // separate variantsArray from articleArray
            $variants = $articleArray['variants'];
            unset($articleArray['variants']);

            /** @var ArticleDetail $mainDetail */
            $mainDetail = $detailRepository->findOneBy(
                ['number' => $articleArray['mainDetail']['number']]
            );

            // article exists in db?
            if ($mainDetail) {
                // update it

                $articleId = $mainDetail->getArticleId();

                $this->updateArticle($articleId, $articleArray);

                foreach ($variants as $variantArray) {
                    /** @var ArticleDetail $detail */
                    $detail = $detailRepository->findOneBy(
                        ['number' => $variantArray['number']]
                    );

                    // variant exists ind db?
                    if ($detail) {
                        // update it
                        $this->updateVariant($detail->getId(), $variantArray);
                    } else {
                        // create it
                        $this->createVariant($articleId, $variantArray);
                    }
                }
            } else {
                // create it

                $articleId = $this->createArticle($articleArray);

                foreach ($variants as $variantArray) {
                    $this->createVariant($articleId, $variantArray);
                }
            }
        }

        return $productIds;
    }

    /**
     * @param $taxRate
     */
    protected function createTax($taxRate) {
        /** @var Repository $taxRepo */
        $taxRepo = Shopware()->Models()->getRepository(
            'Shopware\Models\Tax\Tax'
        );

        $tax = $taxRepo->findOneBy(['tax' => $taxRate]);

        if ( ! $tax) {
            /** @var \Shopware\Models\Tax\Tax $taxRepo */
            $tax = new Tax();

            $tax->setName($taxRate . ' %');
            $tax->setTax($taxRate);
            Shopware()->Models()->persist($tax);
            try {
                Shopware()->Models()->flush($tax);
            } catch (OptimisticLockException $e) {
            }
        }
    }

    /**
     * @param int   $articleId
     * @param array $variantArray
     */
    protected function createVariant(
        $articleId,
        $variantArray
    ) {
        /** @var VariantResource $variantResource */
        $variantResource = ApiManager::getResource('variant');

        $variantArray['articleId'] = $articleId;

        try {
            $variantResource->create($variantArray);
        } catch (NotFoundException $e) {
            // TODO: handle  exception
        } catch (ParameterMissingException $e) {
            // TODO: handle  exception
        } catch (ValidationException $e) {
            // TODO: handle  exception
        }
    }

    /**
     * @param array $articleArray
     *
     * @return null|int
     */
    protected function createArticle($articleArray) {
        /** @var ArticleResource $articleResource */
        $articleResource = ApiManager::getResource('article');

        $articleId = null;

        try {
            /** @var Article $article */
            $article = $articleResource->create($articleArray);
            $articleId = $article->getId();
        } catch (CustomValidationException $e) {
            // TODO: handle  exception
        } catch (ValidationException $e) {
            // TODO: handle  exception
        }

        return $articleId;
    }

    /**
     * @param int   $articleId
     * @param array $articleArray
     */
    protected function updateArticle($articleId, $articleArray) {
        /** @var ArticleResource $articleResource */
        $articleResource = ApiManager::getResource('article');

        try {
            $articleResource->update(
                $articleId,
                $articleArray
            );
        } catch (NotFoundException $e) {
            // TODO: handle  exception
        } catch (ParameterMissingException $e) {
            // TODO: handle  exception
        } catch (ValidationException $e) {
            // TODO: handle  exception
        }
    }

    /**
     * @param int   $variantId
     * @param array $variantArray
     */
    protected function updateVariant($variantId, $variantArray) {
        /** @var VariantResource $variantResource */
        $variantResource = ApiManager::getResource('variant');

        try {
            $variantResource->update($variantId, $variantArray);
        } catch (NotFoundException $e) {
            // TODO: handle  exception
        } catch (ParameterMissingException $e) {
            // TODO: handle  exception
        } catch (ValidationException $e) {
            // TODO: handle  exception
        }
    }

    /**
     * Deletes the articles, that are already in Shopware, but where not,
     * imported or updated.
     *
     * @param int[] $productIds List of all ProductIds, that where either
     *                          imported or updated.
     */
    protected function deleteSurplus($productIds) {
        /** @var ArticleResource $articleResource */
        $articleResource = ApiManager::getResource('article');
        /** @var Article[] $presentArticles */
        $presentArticles = Shopware()
            ->Models()
            ->getRepository('Shopware\Models\Article\Article')
            ->findAll();

        foreach ($presentArticles as $article) {
            /** @var bool $deleteArticle */
            $deleteArticle = ! in_array(
                $article->getAttribute()->getAfterbuyProductid(),
                $productIds
            );

            // article with no afterbuyProductId where never imported from AB
            // but in some cases the array $productIds can contain null values
            $deleteArticle = $deleteArticle
                || is_null(
                    $article->getAttribute()->getAfterbuyProductid()
                );

            if ($deleteArticle) {
                try {
                    $articleResource->delete($article->getId());
                } catch (NotFoundException $e) {
                    // TODO: handle  exception
                } catch (ParameterMissingException $e) {
                    // TODO: handle  exception
                }
            }
        }
    }
}

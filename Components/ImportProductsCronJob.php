<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 16.07.18
 * Time: 10:19
 */

namespace Shopware\FatchipShopware2Afterbuy\Components;


use Doctrine\ORM\OptimisticLockException;
use Shopware\Components\Api\Exception\CustomValidationException;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Exception\ParameterMissingException;
use Shopware\Components\Api\Exception\ValidationException;

// TODO: remove this for productive use
use Shopware\FatchipShopware2Afterbuy\Components\ApiMock as Api;

use Shopware\Components\Api\Resource\Article as ArticleResource;

// TODO: remove this for productive use
// use Shopware\FatchipShopware2Afterbuy\Components\ArticleResourceMock as ArticleResource;

use Shopware\Components\Api\Resource\Variant as VariantResource;

// TODO: remove this for productive use
// use Shopware\FatchipShopware2Afterbuy\Components\VariantResourceMock as VariantResource;

use Shopware\Components\Api\Manager as ApiManager;

// TODO: remove this for productive use
// use Shopware\FatchipShopware2Afterbuy\Components\ApiManagerMock as ApiManager;

use Shopware\Models\Article\Article;
use Shopware\Models\Article\Detail as ArticleDetail;

use Fatchip\Afterbuy\ApiClient;
use Shopware\Models\Article\Detail;
use Shopware\Models\Tax\Repository;
use Shopware\Models\Tax\Tax;


/**
 * Import products from AfterBuy API and import them into shopware
 *
 * @package Shopware\FatchipShopware2Afterbuy\Components
 */
class ImportProductsCronJob {
    /**
     * The entry point of this Class.
     */
    public function importProducts2Shopware() {
        $productsResult = $this->retrieveProductsArray();

        $products = $productsResult['Result']['Products']['Product'];

        $articles = $this->convertProducts2Articles($products);

        $this->importArticles($articles);

        var_dump($productsResult);
    }

    // TODO: remove in productive
    public function call() {
        return $this->retrieveProductsArray();
    }

    /**
     * Call the AfterBuy API and retrieve all the products as array.
     *
     * @return array
     */
    protected function retrieveProductsArray() {
        // Get SDK object
        /** @var ApiClient $apiClient */
        $apiClient = Shopware()->Container()->get('afterbuy_api_client');
        // $apiClient = new ApiMock();


        // Get all products from AfterbuyAPI
        $productsResult = $apiClient->getShopProductsFromAfterbuy();
        if ($productsResult['CallStatus'] != 'Success') {
            // TODO: fix error handling
            die('GetShopProducts: CallStatus: ['
                . $productsResult['CallStatus']
                . '] Awaited: [Success].');
        }
        if ($productsResult['Result']['HasMoreProducts']) {
            // pagination on
        } else {
            // pagination off
        }

        return $productsResult;
    }

    /**
     * Converts products array to articles array.
     *
     * @param array $products
     *
     * @return array
     */
    protected function convertProducts2Articles($products) {
        /** @var array $articles */
        $articles = [];
        $details = [];
        $mainDetails = [];

        // for each product in products
        foreach ($products as $product) {
            // Map article / detail field names

            $productID = $product['ProductID'];

            // variantSet related?
            if (isset($product['BaseProducts'])) {
                // variantSet parent object?
                if ( ! isset($product['BaseProducts']['BaseProduct']['BaseProductID'])) {
                    $currentParentProduct = $product;
                    $currentParentProductID = $productID;

                    $variantSets[$currentParentProductID]
                        = $currentParentProduct;

                    $articles[$currentParentProductID] = $this->mapArticleData(
                        $currentParentProduct
                    );
                    $articles[$currentParentProductID]['variants'] = [];

                    $childProducts
                        = $currentParentProduct['BaseProducts']['BaseProduct'];

                    // foreach variant set product
                    foreach ($childProducts as $currentChildProduct) {
                        $currentChildProductID
                            = $currentChildProduct['BaseProductID'];

                        // is currentChildProduct athe mainDetail for currentParentProduct?
                        if (
                            $currentChildProduct['BaseProductsRelationData']['DefaultProduct']
                            == -1
                        ) {
                            $mainDetails[$currentParentProductID]
                                = $currentChildProductID;
                        }

                        // detail already processed?
                        if (isset($details[$currentChildProductID])) {
                            $articles[$currentParentProductID]
                                = $this->addDetailToArticle(
                                $articles[$currentParentProductID],
                                $details[$currentChildProductID],
                                $mainDetails[$currentParentProductID]
                                == $currentChildProductID
                            );
                        }
                    }
                } // variantSet childObject
                else {
                    $currentChildProductID = $productID;
                    $parentProductID
                        = $product
                    ['BaseProducts']
                    ['BaseProduct']
                    ['BaseProductID'];

                    $details[$currentChildProductID]
                        = $this->mapDetailData($product);

                    // variant set already processed?
                    if (isset($articles[$parentProductID])) {
                        $articles[$parentProductID] = $this->addDetailToArticle(
                            $articles[$parentProductID],
                            $details[$currentChildProductID],
                            $mainDetails[$parentProductID]
                            == $currentChildProductID
                        );
                    }
                }

            } // single product
            else {
                $details[$productID] = $this->mapDetailData($product);

                $articles[$productID] = $this->mapArticleData($product);

                $articles[$productID] = $this->addDetailToArticle(
                    $articles[$productID],
                    $details[$productID],
                    true
                );
            }
        }

        return $articles;
    }

    /**
     * Converts the given product array to an article array, by mapping the
     * relevant fields.
     *
     * @param array $product - Array with product data, as it comes from the
     *                       Afterbuy API.
     *
     * @return array
     */
    protected function mapArticleData($product) {
        // https://community.shopware.com/Artikel-anlegen_detail_807.html
        // https://community.shopware.com/_detail_1778.html
        $article = [
            'name'             => $product['Name'],
            'description'      => $product['ShortDescription'],
            'descriptionLong'  => $product['Description'],
            // TODO: not in article model, but in db
            'shippingtime'     => $product['DeliveryTime'],
            'tax'              => $product['TaxRate'],
            'keywords'         => $product['Keywords'],
            'changed'          => $product['ModDate'],
            'active'           => 1,
            'pseudoSales'      => 0,
            'highlight'        => false,
            'metaTitle'        => '',
            'lastStock'        => $product['Discontinued'] & $product['Stock'],
            'notification'     => false,
            'template'         => '',
            'supplier'         => $product['ProductBrand'],
            'availableFrom'    => null,
            'availableTo'      => null,
            'configuratorSet'  => null,
            'priceGroup'       => null,
            'pricegroupActive' => false,
            'propertyGroup'    => null,
            'crossBundleLook'  => false,

            // TODO: what to map here?

            // could not find field in AB API
            'added'            => null,
            // not sure what kind of mode is meant
            'mode'             => 0,
        ];

        return $article;
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
     * Converts the given product array to an detail array, by mapping the
     * relevant fields. The given product must be variantSet related, therefore
     * $product['BaseProductsRelationData'] must be set.
     *
     * @param array $product - Array with product data, as it comes from the
     *                       Afterbuy API.
     *
     * @return array
     */
    protected function mapDetailData($product) {
        $detail = [
            'number'         => $product['Anr'],
            'supplierNumber' => $product['ManufacturerPartNumber'],
            // don't know what that is
            // seams to be some kind of articleName extension in lastSeenProductsConfig
            'additionalText' => '',
            'active'         => true,
            'inStock'        => $product['Quantity'],
            'stockMin'       => $product['MinimumStock'],
            'lastStock'      => $product['Discontinued'] & $product['Stock'],
            'weight'         => $product['Weight'],
            'ean'            =>
                $product['ManufacturerStandardProductIDType'] == 'EAN'
                    ? $product['ManufacturerStandardProductIDValue']
                    : null,
            'unit'           => $product['UnitOfQuantity'],

            // TODO: not in article model, but in db
            'sales'          => '',

            // TODO: what to map here
            'position'       => $product['Position'],
            'width'          => null,
            'height'         => null,
            'len'            => null,
            'purchaseSteps'  => '',
            'maxPurchase'    => '',
            'minPurchase'    => '',
            'purchaseUnit'   => '',
            'referenceUnit'  => '',
            'packUnit'       => '',
            'releaseDate'    => '',
            'shippingFree'   => '',
            'shippingTime'   => $product['DeliveryTime'],
            'purchasePrice'  => '',
        ];

        return $detail;
    }

    /**
     * Imports the given articles array into shopware.
     *
     * @param array $articles
     */
    protected function importArticles($articles) {
        $detailRepository = Shopware()->Models()->getRepository(
            'Shopware\Models\Article\Detail'
        );

        foreach ($articles as $articleArray) {

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
                    /** @var Detail $detail */
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
    }

    /**
     * Adds the given detail to the given article. When detail is mainDetail,
     * the detail is set to article's mainDetail field. Otherwise the detail is
     * added to the variants array.
     *
     * @param array $article
     * @param array $detail
     * @param bool  $isMainDetail
     *
     * @return mixed
     */
    protected function addDetailToArticle($article, $detail, $isMainDetail) {
        // mainDetail?
        if ($isMainDetail) {
            // add detail as mainDetail
            $article['mainDetail'] = $detail;
        } else {
            // add detail as variant
            array_push($article['variants'], $detail);
        }

        return $article;
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
    protected function updateArticle(
        $articleId,
        $articleArray
    ) {
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
}

<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 16.07.18
 * Time: 10:19
 */

namespace Shopware\FatchipShopware2Afterbuy\Components;


use Shopware\Components\Api\Resource\Article as ArticleResource;
use Shopware\Components\Api\Manager as ApiManager;
use Shopware\Models\Article\Detail as ArticleDetail;

use Fatchip\Afterbuy\ApiClient;


class ImportProductsCronJob {
    /**
     * @return int[]
     */
    public function importProducts2Shopware() {
        $productsResult = $this->retrieveProductsArray();

        $products = $productsResult['Result']['Products']['Product'];

        $articles = $this->convertProducts2ArticlesArray($products);

        $this->addArticles($articles);
    }

    /**
     * @param array $product - Array with product data, as it comes from the
     *                       Afterbuy API.
     *
     * @return array
     */
    protected function createArticleArray(array $product) {
        $article = [];

        $article['name'] = $product['Name'];
        $article['description'] = $product['ShortDescription'];
        $article['descriptionLong'] = $product['Description'];
        $article['shippingtime'] = $product['DeliveryTime'];
        $article['tax'] = $product['TaxRate'];
        $article['keywords'] = $product['Keywords'];
        $article['changed'] = $product['ModDate'];

        // TODO: what to map here?
        $article['datum'] = $product[''];
        $article['active'] = 1;
        $article['pseudosales'] = 0;
        $article['topseller'] = $product[''];
        $article['metaTitle'] = $product[''];
        $article['pricegroupID'] = $product[''];
        $article['pricegroupActive'] = 0;
        $article['filtergroupID'] = $product[''];
        $article['laststock'] = $product['Discontinued'] & $product['Stock'];
        $article['crossbundleloock'] = $product[''];
        $article['notification'] = 0;
        $article['template'] = '';
        $article['mode'] = 0;

        return $article;
    }

    /**
     * @param array $product - Array with product data, as it comes from the
     *                       Afterbuy API.
     *
     * @return array
     */
    protected function createDetailArray(array $product) {
        $detail = [];

        $detail['number'] = $product['Anr'];
        $detail['supplierNumber'] = $product['ManufacturerPartNumber'];
        $detail['shippingTime'] = $product['DeliveryTime'];
        $detail['laststock'] = $product['Discontinued'] & $product['Stock'];

        return $detail;
    }

    protected function convertProducts2ArticlesArray($products) {
        $articles = [];
        $details = [];
        $mainDetailsMap = [];

        // for each product in products
        foreach ($products as $product) {
            // Map article / detail field names

            $productID = $product['ProductID'];

            // variantSet related?
            if (isset($product['BaseProducts'])) {
                // variantSet parent object?
                if ($product['Anr'] == '0') {
                    $currentParentProduct = $product;
                    $currentParentProductID = $productID;

                    $variantSets[$currentParentProductID]
                        = $currentParentProduct;

                    $articles[$currentParentProductID]
                        = $this->createArticleArray($currentParentProduct);
                    $articles[$currentParentProductID]['variants'] = [];

                    // foreach variant set product
                    foreach (
                        $currentParentProduct['BaseProducts']['BaseProduct'] as
                        $currentChildProduct
                    ) {
                        $currentChildProductID
                            = $currentChildProduct['BaseProductID'];

                        // mainDetail?
                        if ($currentChildProduct['BaseProductsRelationData']['DefaultProduct']
                            == -1
                        ) {
                            $mainDetailsMap[$currentParentProductID]
                                = $currentChildProductID;
                        }

                        // detail already processed?
                        if (isset($details[$currentChildProductID])) {
                            $articles = $this->addDetailToArticle(
                                $articles,
                                $details,
                                $mainDetailsMap,
                                $currentParentProductID,
                                $currentChildProductID
                            );
                        }
                    }
                    // variantSet childObject
                } else {
                    $currentChildProductID = $productID;
                    $parentProductID
                        = $product['BaseProducts']['BaseProduct']['BaseProductID'];

                    $details[$currentChildProductID]
                        = $this->createDetailArray($product);

                    // variant set already processed?
                    if (isset($articles[$parentProductID])) {
                        $articles = $this->addDetailToArticle(
                            $articles,
                            $details,
                            $mainDetailsMap,
                            $parentProductID,
                            $currentChildProductID
                        );
                    }
                }


            } else {
                // single product
                $details[$productID] = $this->createDetailArray($product);

                $articles[$productID] = $this->createArticleArray($product);

                $articles = $this->addDetailToArticle(
                    $articles,
                    $details,
                    $mainDetailsMap,
                    $productID,
                    $productID
                );
            }
        }

        return $articles;
    }


    // TODO: fix this to use the triggerAction
    protected function addArticles($articles) {
        /** @var ArticleResource $resource */
        $articleResource = ApiManager::getResource('Article');
        /** @var Shopware/Components/Api/ $detailResource */
        $detailResource = ApiManager::getResource('Article');

        foreach ($articles as $articleArray) {
            $modelManager = Shopware()->Models();
            $repo = $modelManager->getRepository(
                'Shopware\Models\Article\Detail'
            );
            /** @var ArticleDetail[] $mainDetail_AB */
            $mainDetail_AB = $articleArray['mainDetail'];
            /** @var ArticleDetail[] $mainDetail_SW */
            $mainDetail_SW = $repo->findBy(
                ['number' => $mainDetail_AB['number']]
            );

            // article exists in db?
            if ($mainDetail_SW) {
                // if article has changed
                // update it
                // else do nothing
                //
                // SW handles updates itself - Yay
                $articleResource->update(
                    $mainDetail_SW[0]->getArticleId(),
                    $articleArray
                );
            } // else create it
            else {
                $variants = $articleArray['variants'];
                unset($articleArray['variants']);

                $result = $articleResource->create($articleArray);
//                $result = $client->post('articles', $article);

                $articleId = json_decode($result)->data->id;

                foreach ($variants as $variant) {
                    $variant['articleId'] = $articleId;
                    $result = $articleResource->create($articleArray);
//                    $result = $client->post('variants', $variant);
                }

                echo $result;
                echo $articleId;
            }
        }
    }

    /**
     * @return array
     */
    protected function retrieveProductsArray() {
        // Get SDK object
        /** @var ApiClient $apiClient */
//        $apiClient = Shopware()->Container()->get('afterbuy_api_client');
        $apiClient = new ApiMock();


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
     * Adds the given detail to the given article. When detail is mainDetail,
     * the detail is set article's mainDetail field. Otherwise the detail is
     * added to the details array.
     * TODO: improve documentation
     *
     * @param array $articles
     * @param array $details
     * @param array $mainDetailsMap
     * @param int   $articleProductID
     * @param int   $detailProductID
     *
     * @return mixed
     */
    protected function addDetailToArticle(
        $articles,
        $details,
        $mainDetailsMap,
        $articleProductID,
        $detailProductID
    ) {
        $isMainDetail
            = $mainDetailsMap[$articleProductID]
            == $detailProductID;
        // mainDetail?
        if ($isMainDetail) {
            $articles[$articleProductID]['mainDetail']
                = $details[$detailProductID];
        } else {
            array_push(
                $articles[$articleProductID]['variants'],
                $details[$detailProductID]
            );
        }

        return $articles;
    }
}

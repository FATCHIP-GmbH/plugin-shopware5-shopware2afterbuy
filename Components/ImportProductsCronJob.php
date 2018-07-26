<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 16.07.18
 * Time: 10:19
 */

namespace Shopware\FatchipShopware2Afterbuy\Components;


use Shopware\Components\Api\Resource\Article as ArticleResource;
use Fatchip\Afterbuy\ApiClient;
use Shopware\Components\Api\Manager as ApiManager;
use Shopware\Models\Article\Detail as ArticleDetail;
use Shopware\Models\Tax\Tax;


class ImportProductsCronJob {
    /** @var array The fields, that are objects in SW detail */
    protected $test = [
    ];

    /**
     * @return int[]
     */
    public function importProducts2Shopware() {
        // Get SDK object
        /** @var ApiClient $apiClient */
        $apiClient = Shopware()->Container()->get('afterbuy_api_client');
        /** @var ArticleResource $resource */
        $articleResource = ApiManager::getResource('Article');


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
        $lastProductID = $productsResult['Result']['LastProductID'];

        $products = $productsResult['Result']['Products']['Product'];

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

                    $variantSets[$currentParentProductID] = $currentParentProduct;

                    $articles[$currentParentProductID] = $this->createArticleArray($currentParentProduct);
                    $articles[$currentParentProductID]['details'] = [];

                    // foreach variant set product
                    foreach ($currentParentProduct['BaseProducts']['BaseProduct'] as $currentChildProduct) {
                        $currentChildProductID = $currentChildProduct['BaseProductID'];

                        // mainDetail?
                        if ($currentChildProduct['BaseProductsRelationData']['DefaultProduct'] == -1) {
                            $mainDetailsMap[$currentParentProductID] = $currentChildProductID;
                        }

                        // detail already processed?
                        if (isset($details[$currentChildProductID])) {
                            $isMainDetail = $mainDetailsMap[$currentParentProductID] == $currentChildProductID;
                            $this->addDetailToArticle(
                                $isMainDetail,
                                $articles[$currentParentProductID],
                                $details[$currentChildProductID]
                            );
                        }
                    }
                // variantSet childObject
                } else {
                    $currentChildProductID = $productID;
                    $parentProductID = $product['BaseProducts']['BaseProduct']['BaseProductID'];

                    $details[$currentChildProductID] = $this->createDetailArray($product);

                    // variant set already processed?
                    if (isset($articles[$parentProductID])) {
                        $isMainDetail = $mainDetailsMap[$parentProductID] == $currentChildProductID;
                        $this->addDetailToArticle(
                            $isMainDetail,
                            $articles[$parentProductID],
                            $details[$currentChildProductID]
                        );
                    }
                }


            } else {
                // single product
                $details[$productID] = $this->createDetailArray($product);

                $articles[$productID] = $this->createArticleArray($product);
                $this->addDetailToArticle(
                    true,
                    $articles[$productID],
                    $details[$productID]
                );
            }

        }

        foreach ($articles as $article) {
            $repo = Shopware()
                ->Models()
                ->getRepository('Shopware\Models\Article\Detail');
            /** @var ArticleDetail[] $a */
            $mainDetail_AB = $article['mainDetail'];
            $mainDetail_SW = $repo->findBy(['number' => $mainDetail_AB['number']]);

            // if article exists in db
            if ($mainDetail_SW) {
                foreach ($mainDetail_AB as $key => $value) {
                    $setter = 'set' . ucfirst($key);
                    $getter = 'get' . ucfirst($key);

                    // getter exists in Shopware detail?
                    if (method_exists($mainDetail_SW[0], $getter)) {
                        $this->updateValue(
                            $mainDetail_SW[0],
                            $getter,
                            $setter,
                            $value
                        );
                    }
                }

                foreach ($mainDetail_AB as $key => $value) {

                }
                // if article has changed
                    // update it
                // else do nothing
            }
            // else create it
            else {
                $articleResource->create($article);
            }
        }

        return $productsResult;
    }

    protected function updateValue($mainDetail_SW, $getter, $setter, $value_AB) {
        // compare value sw - ab
        // setter exists in Shopware detail?
        if (method_exists($mainDetail_SW, $setter)) {
            // overwrite it in Shopware
            $mainDetail_SW->$setter($value_AB);
        }
    }

    /**
     * @param array $product - Array with product data, as it comes from the
     *                       Afterbuy API.
     *
     * @return array
     */
    protected function createArticleArray(array $product) {
        $articles = [];

        /** @var Tax $taxObject */
        $taxObject = Shopware()
            ->Models()
            ->getRepository('Shopware\Models\Tax\Tax')
            ->findOneBy(['tax' => $product['TaxRate']]);

        if ( ! $taxObject) {
            // TODO: test, that the new tax does not break the shop. Do we need a country relation?
            $taxObject = new Tax();
            $taxObject->setTax($product['TaxRate']);
            $taxObject->setName($product['TaxRate'] . '%');
        }

        $articles['name'] = $product['Name'];
        $articles['description'] = $product['ShortDescription'];
        $articles['descriptionLong'] = $product['Description'];
        $articles['shippingtime'] = $product['DeliveryTime'];
        $articles['tax'] = $product['TaxRate'];
        $articles['keywords'] = $product['Keywords'];
        $articles['changed'] = $product['ModDate'];

        // TODO: what to map here?
        $articles['datum'] = $product[''];
        $articles['active'] = 1;
        $articles['pseudosales'] = 0;
        $articles['topseller'] = $product[''];
        $articles['metaTitle'] = $product[''];
        $articles['pricegroupID'] = $product[''];
        $articles['pricegroupActive'] = 0;
        $articles['filtergroupID'] = $product[''];
        $articles['laststock'] = $product['Discontinued'] & $product['Stock'];
        $articles['crossbundleloock'] = $product[''];
        $articles['notification'] = 0;
        $articles['template'] = '';
        $articles['mode'] = 0;

        return $articles;
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

        return $detail;
    }

    /**
     * Adds the given detail to the given article. When detail is mainDetail,
     * the detail is set article's mainDetail field. Otherwise the detail is
     * added to the details array.
     *
     * @param array $isMainDetail
     * @param array $article
     * @param array $detail
     */
    protected function addDetailToArticle($isMainDetail, &$article, $detail) {
        // mainDetail?
        if ($isMainDetail) {
            $article['mainDetail'] = $detail;
        } else {
            array_push(
                $article['details'],
                $detail
            );
        }
    }
}
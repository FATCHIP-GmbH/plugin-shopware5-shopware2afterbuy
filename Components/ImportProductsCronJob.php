<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 16.07.18
 * Time: 10:19
 */

namespace Shopware\FatchipShopware2Afterbuy\Components;


use Shopware\Components\Api\Manager as ApiManager;
use Shopware\Models\Article\Detail as ArticleDetail;
use Shopware\Models\Tax\Tax;


class ImportProductsCronJob {
    /**
     * @return int[]
     */
    public function importProducts2Shopware() {
        // Get SDK object
        $apiClient = Shopware()->Container()->get('afterbuy_api_client');
        /** @var \Shopware\Components\Api\Resource\Article $resource */
        $articleResource = ApiManager::getResource('article');


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
        $variantSets = [];
        $variantProducts = [];
        $articles = [];
        $details = [];

        // for each productsResult
        foreach ($products as $product) {
            // Map article field names
            $productID = $product['ProductID'];

            // variantSet related?
            if (isset($product['BaseProducts'])) {
                // variantSet parent object?
                if ($product['Anr'] == '0') {
                    $variantSets[$productID] = $product;

                    $articles[$productID] = $this->createArticleArray($product);
                } else {
                    $variantProducts[$productID] = $product;

                    $details[$productID] = $this->createDetailArray($product);
                }
            } else {
                // single product
                $details[$productID] = $this->createDetailArray($product);

                $articles[$productID] = $this->createArticleArray($product);
                $articles[$productID]['mainDetail'] = $details[$productID];
            }

//            try {
//                $articleArray = $this->mapProductToDetail($product, $products);
//                var_dump($articleArray);
//            } catch (InvalidArgumentException $e) {
//            }
            //     if article exists in db
            //         if article has changed
            //             update it
            //         else do nothing
            //     else create it
//            try {
//                $article = $articleResource->create($articleArray);
//            } catch (CustomValidationException $e) {
//                // TODO: implement
//                die('CustomValidationException: ' . $e);
//            } catch (ValidationException $e) {
//                // TODO: implement
//                die('ValidationException: ' . $e);
//            }
        }

        $repo = Shopware()
            ->Models()
            ->getRepository('Shopware\Models\Article\Detail');
        /** @var ArticleDetail[] $a */
        $a = $repo->findBy(['article' => 5]);

        return $productsResult;
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
        $articles['description_long'] = $product['Description'];
        $articles['shippingtime'] = $product['DeliveryTime'];
        $articles['tax'] = $taxObject;
        $articles['keywords'] = $product['Keywords'];
        $articles['changetime'] = $product['ModDate'];

        // TODO: what to map here?
        $articles['datum'] = $product[''];
        $articles['active'] = $product[''];
        $articles['pseudosales'] = $product[''];
        $articles['topseller'] = $product[''];
        $articles['metaTitle'] = $product[''];
        $articles['pricegroupID'] = $product[''];
        $articles['pricegroupActive'] = $product[''];
        $articles['filtergroupID'] = $product[''];
        $articles['laststock'] = $product[''];
        $articles['crossbundleloock'] = $product[''];
        $articles['notification'] = $product[''];
        $articles['template'] = $product[''];
        $articles['mode'] = $product[''];

        return $articles;
    }

    /**
     * @param array $product - Array with product data, as it comes from the
     *                       Afterbuy API.
     *
     * @return array
     */
    protected function createDetailArray(array $product) {
        $details = [];

        $details['ordernumber'] = $product['Anr'];
        $details['suppliernumber'] = $product['ManufacturerPartNumber'];
        $details['shippingtime'] = $product['DeliveryTime'];

        return $details;
    }
}
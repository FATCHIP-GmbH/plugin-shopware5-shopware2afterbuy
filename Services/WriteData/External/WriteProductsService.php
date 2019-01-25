<?php

namespace FatchipAfterbuy\Services\WriteData\External;

use Fatchip\Afterbuy\ApiClient;
use FatchipAfterbuy\Components\Helper;
use FatchipAfterbuy\Services\Helper\ShopwareArticleHelper;
use FatchipAfterbuy\Services\WriteData\AbstractWriteDataService;
use FatchipAfterbuy\Services\WriteData\WriteDataInterface;
use FatchipAfterbuy\ValueObjects\Article;
use Shopware\Models\Article\Detail;
use Shopware\Models\Customer\Group;


class WriteProductsService extends AbstractWriteDataService implements WriteDataInterface {

    /**
     * @param array $data
     * @return mixed|void
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function put(array $data) {
        $data = $this->transform($data);
        return $this->send($data);
    }

    /**
     * transforms valueObject into final structure for storage
     * could may be moved into separate helper
     *
     * @param array $data
     * @return array
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function transform(array $data) {
        /**
         * @var Group $customerGroup
         */

        $api = new ApiClient($this->apiConfig);

        $products = array(
            'Products' => array(

            )
        );

        $afterbuyProductIds = [];

        //1. add simple articles
        foreach($data as $value) {
            /**
             * @var \FatchipAfterbuy\ValueObjects\Article $value
             */
            if($value->getVariantArticles()) {
                continue;
            }

            //TODO: into component
            $product = array(
              'Product' => array(
                  'ProductIdent' => array(
                      'ProductInsert' => 1,
                      'Anr' => $value->getVariantId()
                  ),
                  'EAN' => $value->getInternalIdentifier(),
                  'Anr' => (string) $value->getVariantId(),
                  'Name' => $value->getName(),
                  'ManufacturerPartNumber' => $value->getSupplierNumber(),
                  'Description' => $value->getDescription(),
                  'ShortDescription' => $value->getShortDescription(),
                  'Quantity' => $value->getStock(),
                  'UnitOfQuantity' => 'Stk',
                  'MinimumStock' => $value->getStockMin(),
                  'SellingPrice' => Helper::convertNumberToABString($value->getPrice()),
                  'TaxRate' => Helper::convertNumberToABString($value->getTax()),
                  'ProductBrand' => $value->getManufacturer(),
               )
            );

            $products['Products'][] = $product;


        }
        $response = $api->updateShopProducts($products);

        //TODO: handle no result or log on error

        if(array_key_exists('Result', $response)) {

            if (array_key_exists('ProductID', $response["Result"]["NewProducts"]["NewProduct"])) {
                $internalArticleNumber = $response["Result"]["NewProducts"]["NewProduct"]["Anr"];
                $afterbuyProductIds[$internalArticleNumber] = $response["Result"]["NewProducts"]["NewProduct"]["ProductID"];
            } elseif (is_array($response["Result"]["NewProducts"]["NewProduct"][0])) {

                foreach ($response["Result"]["NewProducts"]["NewProduct"] as $newProduct) {
                    $internalArticleNumber = $newProduct["Anr"];
                    $afterbuyProductIds[$internalArticleNumber] = $newProduct["ProductID"];
                }
            }
        }

        foreach ($data as $value) {
            if(!$value->getVariantArticles()) {
                continue;
            }

            $products = array(
                'Products' => array(
                )
            );

            foreach($value->getVariantArticles() as $variant) {
                //EAN for variant, Anr for main articles

                $variants = [];
                $variantName = "";

                /**
                 * @var Article $variant
                 */

                foreach ($variant->getVariants() as $group => $option) {
                    $variants[] = array(
                        'AddAttribute' => array(
                            'AttributName' => key($option),
                            'AttibutValue' => reset($option),
                            'AttributTyp' => 1,
                            'AttributRequired' => 1
                        )
                    );

                    $variantName .= reset($option) . " ";
                }

                $product = array(
                    'Product' => array(
                        'ProductIdent' => array(
                            'ProductInsert' => 1,
                            'Anr' => $variant->getVariantId()
                        ),
                        'Anr' => $variant->getVariantId(),
                        'EAN' => $variant->getInternalIdentifier(),
                        'Name' => $value->getName() . " " . $variantName,
                        'ManufacturerPartNumber' => $variant->getSupplierNumber(),
                        'Description' => $variant->getDescription(),
                        'ShortDescription' => $variant->getShortDescription(),
                        'Quantity' => $variant->getStock(),
                        'UnitOfQuantity' => 'Stk',
                        'MinimumStock' => $variant->getStockMin(),
                        'SellingPrice' => Helper::convertNumberToABString($variant->getPrice()),
                        'TaxRate' => Helper::convertNumberToABString($variant->getTax()),
                        'ProductBrand' => $value->getManufacturer(),
                        'AddAttributes' => $variants
                    )
                );

                $products['Products'][] = $product;
            }

            $variantIds = [];

            $response = $api->updateShopProducts($products);

            if(array_key_exists('Result', $response)) {

                if (array_key_exists('ProductID', $response["Result"]["NewProducts"]["NewProduct"])) {
                    $internalArticleNumber = $response["Result"]["NewProducts"]["NewProduct"]["Anr"];
                    $variantIds[$internalArticleNumber] = $response["Result"]["NewProducts"]["NewProduct"]["ProductID"];
                } elseif (is_array($response["Result"]["NewProducts"]["NewProduct"][0])) {

                    foreach ($response["Result"]["NewProducts"]["NewProduct"] as $newProduct) {
                        $internalArticleNumber = $newProduct["Anr"];
                        $variantIds[$internalArticleNumber] = $newProduct["ProductID"];
                    }
                }
            }

            $afterbuyProductIds = $afterbuyProductIds + $variantIds;

            //TODO: keep order of options
            //TODO: update functionality
            //TODO: set variant name in read service

            $variantArticles = [];

            foreach($value->getVariantArticles() as $variant) {
                if(!$variant->getExternalIdentifier()) {
                    $variant->setExternalIdentifier($afterbuyProductIds[$variant->getVariantId()]);
                }

                $variantArticles[] = array(
                    'AddBaseProduct' => array(
                        'ProductID' => $variant->getExternalIdentifier(),
                        'ProductLabel' => $value->getName(),
                        'ProductQuantity' => $variant->getStock()
                    )
                );
            }

            $products['Products'] = array(
                'Product' => array(
                    'ProductIdent' => array(
                        'ProductInsert' => 1,
                        'Anr' => $value->getMainArticleId(),
                        'BaseProductType' => 1
                    ),
                    'Anr' => $value->getMainArticleId(),
                    'EAN' => $value->getInternalIdentifier(),
                    'Name' => $value->getName(),
                    'Description' => $value->getDescription(),
                    'ShortDescription' => $value->getShortDescription(),
                    'UnitOfQuantity' => 'Stk',
                    'TaxRate' => Helper::convertNumberToABString($value->getTax()),
                    'ProductBrand' => $value->getManufacturer(),
                    'AddBaseProducts' => $variantArticles
                )
            );

            $response = $api->updateShopProducts($products);
        }

        return $products;
    }


    /**
     * @param $targetData
     * @return mixed|void
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function send($targetData) {


       //TODO: update modDate
    }
}
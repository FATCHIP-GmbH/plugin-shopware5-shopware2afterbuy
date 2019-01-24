<?php

namespace FatchipAfterbuy\Services\WriteData\External;

use Fatchip\Afterbuy\ApiClient;
use FatchipAfterbuy\Components\Helper;
use FatchipAfterbuy\Services\Helper\ShopwareArticleHelper;
use FatchipAfterbuy\Services\WriteData\AbstractWriteDataService;
use FatchipAfterbuy\Services\WriteData\WriteDataInterface;
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

            $product = array(
              'Product' => array(
                  'ProductIdent' => array(
                      'ProductInsert' => 1,
                      'EAN' => $value->getInternalIdentifier()
                  ),
                  'EAN' => $value->getInternalIdentifier(),
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
        //TODO: store afterbuy product id
        $response = $api->updateShopProducts($products);

        //TODO: one or multiple result
/*        if($response["Result"]["NewProducts"]["NewProduct"]["ProductID"]) {
            $afterbuyProductIds[$value->getMainArticleId()] = $response["Result"]["NewProducts"]["NewProduct"]["ProductID"];
            $value->setExternalIdentifier($response["Result"]["NewProducts"]["NewProduct"]["ProductID"]);
        }
        else {
            $this->logger->error('Error storing Products', $response);
        }*/

        //2. base products from variant articles where no afterbuy id is stored

        foreach ($data as $value) {
            if(!$value->getVariantArticles()) {
                continue;
            }

            if(!$value->getExternalIdentifier()) {

                $products['Products'] = array(
                    'Product' => array(
                        'ProductIdent' => array(
                            'ProductInsert' => 1,
                            'EAN' => $value->getInternalIdentifier(),
                            'BaseProductType' => 1
                        ),
                        //TODO: generate internal identifier for base variant
                        'EAN' => $value->getInternalIdentifier(),
                        'Name' => $value->getName(),
                        'Description' => $value->getDescription(),
                        'ShortDescription' => $value->getShortDescription(),
                        'UnitOfQuantity' => 'Stk',
                        'TaxRate' => Helper::convertNumberToABString($value->getTax()),
                        'ProductBrand' => $value->getManufacturer(),
                    )
                );

                $response = $api->updateShopProducts($products);

                if($response["Result"]["NewProducts"]["NewProduct"]["ProductID"]) {
                    $afterbuyProductIds[$value->getMainArticleId()] = $response["Result"]["NewProducts"]["NewProduct"]["ProductID"];
                    $value->setExternalIdentifier($response["Result"]["NewProducts"]["NewProduct"]["ProductID"]);
                }
                else {
                    continue;
                    $this->logger->error('Error storing Product', $response);
                }

                //TODO: store to after buy id in db (all at once)

            }



            //TODO: store variants
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
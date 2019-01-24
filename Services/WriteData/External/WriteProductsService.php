<?php

namespace FatchipAfterbuy\Services\WriteData\External;

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

        $products = array(
            'Products' => array(

            )
        );

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
                      //TODO: set
                      'BaseProductType' => '',
                      'UserProductID' => $value->getInternalIdentifier(),
                      'ProductID' => $value->getExternalIdentifier(),
                      'EAN' => $value->getEan()
                  ),
                  'EAN' => $value->getEan(),
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

            //TODO: Varianten

            $products['Products'][] = $product;


        }



        return array();
    }


    /**
     * @param $targetData
     * @return mixed|void
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function send($targetData) {
       $this->entityManager->flush();

       //TODO: update modDate
    }
}
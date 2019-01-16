<?php

namespace FatchipAfterbuy\Services\ReadData\External;

use Fatchip\Afterbuy\ApiClient;
use FatchipAfterbuy\Components\Helper;
use FatchipAfterbuy\Services\ReadData\AbstractReadDataService;
use FatchipAfterbuy\Services\ReadData\ReadDataInterface;
use FatchipAfterbuy\ValueObjects\Address;
use FatchipAfterbuy\ValueObjects\Article;
use FatchipAfterbuy\ValueObjects\Order;
use FatchipAfterbuy\ValueObjects\OrderPosition;

class ReadProductsService extends AbstractReadDataService implements ReadDataInterface {

    /**
     * @param array $filter
     * @return array|null
     */
    public function get(array $filter) {
        $data = $this->read($filter);
        return $this->transform($data);
    }

    /**
     * transforms api input into valueObject (targetEntity)
     *
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function transform(array $data) {
        if($this->targetEntity === null) {
            return array();
        }

        $targetData = array();

        foreach($data as $entity) {

            if(empty($entity)) {
                continue;
            }

            /**
             * @var Article $value
             */
            $value = new $this->targetEntity();
            $value->setEan($entity["EAN"]);
            $value->setName($entity["Name"]);
            $value->setExternalIdentifier($entity["ProductID"]);
            $value->setPrice(Helper::convertDeString2Float($entity["SellingPrice"]));
            $value->setManufacturer($entity["ProductBrand"]);
            $value->setStock($entity["Quantity"]);
            $value->setStockMin(intval($entity["MinimumStock"]));
            $value->setTax(Helper::convertDeString2Float($entity["TaxRate"]));

            if(array_key_exists('Attributes', $entity) && array_key_exists('BaseProducts', $entity)) {
                $value->setMainArticleId($entity["BaseProducts"]["BaseProduct"]["BaseProductID"]);


                if (array_key_exists('AttributName', $entity["Attributes"]["Attribut"])) {
                    $variants = array(
                        'option' => $entity["Attributes"]["Attribut"]["AttributName"],
                        'value' => $entity["Attributes"]["Attribut"]["AttributValue"]
                    );
                } else {
                    $variants = [];

                    foreach ($entity["Attributes"]["Attribut"] as $option) {
                        $variant = array(
                            'option' => $option["AttributName"],
                            'value' => $option["AttributValue"]
                        );

                        array_push($variants, $variant);
                    }
                }

                if (!empty($variants)) {
                    $value->setVariants($variants);
                }
            }

            array_push($targetData, $value);
        }

        return $targetData;
    }


    /**
     * provides api data. dummy data as used here can be used in tests
     *
     * @param array $filter
     * @return array
     */
    public function read(array $filter) {

        $resource = new ApiClient($this->apiConfig);
        $data = $resource->getAllShopProductsFromAfterbuy($filter);

        if(!$data || empty($data)) {
            return array();
        }

        return $data;
    }
}
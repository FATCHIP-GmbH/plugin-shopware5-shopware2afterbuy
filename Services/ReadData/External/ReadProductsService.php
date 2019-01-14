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

        //TODO: add datefilter
        //TODO: add pagination
        $filter = array(
            'Filter' => array(
                'FilterName' => 'DateFilter',
                'FilterValues' => array(
                    'DateFrom' => '10.01.2019',
                    'FilterValue' => 'ModDate'
                )
            )
        );

        $resource = new ApiClient($this->apiConfig);
        $data = $resource->getAllShopProductsFromAfterbuy($filter);

        if(!$data || empty($data)) {
            return array();
        }

        return $data;
    }
}
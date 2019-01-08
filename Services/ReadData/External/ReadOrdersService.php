<?php

namespace FatchipAfterbuy\Services\ReadData\External;

use Fatchip\Afterbuy\ApiClient;
use FatchipAfterbuy\Services\ReadData\AbstractReadDataService;
use FatchipAfterbuy\Services\ReadData\ReadDataInterface;
use FatchipAfterbuy\ValueObjects\Category;

class ReadOrdersService extends AbstractReadDataService implements ReadDataInterface {

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
     * @return array|null
     */
    public function transform(array $data) {
        if($this->targetEntity === null) {
            return null;
        }

        $targetData = array();

        foreach($data as $entity) {

            /**
             * @var Category $value
             */
            $value = new $this->targetEntity();

            /*//mappings for valueObject
            $value->setName($entity["Name"]);
            $value->setExternalIdentifier($entity["CatalogID"]);
            $value->setDescription($entity["Description"]);
            $value->setParentIdentifier($entity["ParentID"]);*/

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
        $data = $resource->getOrdersFromAfterbuy();

        if(!$data || !$data["Result"]) {
            return null;
        }

        return $data;
    }
}
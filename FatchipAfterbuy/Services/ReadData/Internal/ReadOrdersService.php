<?php

namespace FatchipAfterbuy\Services\ReadData\Internal;

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

        $this->logger->info("Got " . count($data) . " items", array("Orders", "Read", "Internal"));

        $targetData = array();

        foreach($data as $entity) {

            /**
             * @var Category $value
             */
            $value = new $this->targetEntity();

            //mappings for valueObject


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

        //TODO: implement read data

        if(!$data) {
            $this->logger->error("No data received", array("Orders", "Read", "Internal"));
        }

        return $data;
    }
}
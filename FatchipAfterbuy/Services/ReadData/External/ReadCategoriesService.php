<?php

namespace FatchipAfterbuy\Services\ReadData\External;

use FatchipAfterbuy\Services\ReadData\AbstractReadDataService;
use FatchipAfterbuy\Services\ReadData\ReadDataInterface;
use FatchipAfterbuy\ValueObjects\Category;

class ReadCategoriesService extends AbstractReadDataService implements ReadDataInterface {

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

            //mappings for valueObject
            $value->setName($entity["Name"]);
            $value->setExternalIdentifier($entity["CatalogID"]);
            $value->setDescription($entity["Description"]);

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

        return array(
            array('Name' => 'Testkategorie1',
                'CatalogID' => 1,
                'ParentID' => 0,
                'Description' => 'Eine Beispielkategporie',
                'Position' => 1,
                'Show' => true,
                'Picture1' => ''
            ),
            array('Name' => 'Tochterkategorie1',
                'CatalogID' => 2,
                'ParentID' => 1,
                'Description' => 'Eine Beispielkategporie',
                'Position' => 1,
                'Show' => true,
                'Picture1' => ''
            ),
            array('Name' => 'Testkategorie2',
                'CatalogID' => 3,
                'ParentID' => 0,
                'Description' => 'Eine Beispielkategporie',
                'Position' => 1,
                'Show' => true,
                'Picture1' => ''
            )
        );
    }
}
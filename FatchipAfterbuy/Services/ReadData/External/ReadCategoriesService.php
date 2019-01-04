<?php

namespace FatchipAfterbuy\Services\ReadData\External;

use FatchipAfterbuy\Services\ReadData\AbstractReadDataService;
use FatchipAfterbuy\Services\ReadData\ReadDataInterface;
use FatchipAfterbuy\ValueObjects\Category;

class ReadCategoriesService extends AbstractReadDataService implements ReadDataInterface {

    public function get(array $filter) {
        $data = $this->read($filter);
        return $this->transform($data);
    }

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

    //TODO: just a dummy as it will be used by tests (injected)
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
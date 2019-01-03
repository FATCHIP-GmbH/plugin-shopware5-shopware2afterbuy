<?php

namespace FatchipAfterbuy\Services\ReadData\External;

use FatchipAfterbuy\Services\ReadData\AbstractReadDataService;
use FatchipAfterbuy\Services\ReadData\ReadDataInterface;

class ReadCategoriesService extends AbstractReadDataService implements ReadDataInterface {

    public function get(array $filter) {
        $data = $this->read($filter);
        return $this->transform($data);
    }

    public function transform(array $data) {

    }

    //TODO: just a dummy as it will be used by tests (injected)
    public function read(array $filter) {

        //TODO: add attribute for catalogid
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
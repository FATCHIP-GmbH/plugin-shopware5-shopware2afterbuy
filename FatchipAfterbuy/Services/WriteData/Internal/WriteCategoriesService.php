<?php

namespace FatchipAfterbuy\Services\WriteData\Internal;

use FatchipAfterbuy\Services\WriteData\AbstractWriteDataService;
use FatchipAfterbuy\Services\WriteData\WriteDataInterface;


class WriteCategoriesService extends AbstractWriteDataService implements WriteDataInterface {

    public function put(array $data) {
        $data = $this->transform($data);
        return $this->send($data);
    }

    //TODO: transform handler?
    public function transform(array $data) {
        //TODO: into component

        foreach($data as $value) {
            //TODO: get category
            //TODO: set category values
        }
    }

    /**
     *
     * Type varies depending on target plattform
     * @param $targetData
     */
    public function send($targetData) {
        $this->entityManager->flush();
    }
}
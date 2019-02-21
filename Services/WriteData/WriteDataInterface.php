<?php

namespace abaccAfterbuy\Services\WriteData;

interface WriteDataInterface {

    /**
     * @param array $data
     * @return mixed
     */
    public function put(array $data);

    /**
     * @param array $data
     * @return mixed
     */
    public function transform(array $data);

    /**
     * @param $targetData
     * @return mixed
     */
    public function send($targetData);
}
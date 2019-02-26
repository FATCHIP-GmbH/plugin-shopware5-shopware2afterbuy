<?php

namespace viaebShopwareAfterbuy\Services\ReadData;

interface ReadDataInterface {
    /**
     * @param array $filter
     * @return mixed
     */
    public function get(array $filter);

    /**
     * @param array $data
     * @return mixed
     */
    public function transform(array $data);

    /**
     * @param array $filter
     * @return mixed
     */
    public function read(array $filter);
}
<?php

namespace FatchipAfterbuy\Services\ReadData;

interface ReadDataInterface {
    public function get(array $filter);

    //TODO: inject target entity
    public function transform(array $data);

    public function read(array $filter);
}
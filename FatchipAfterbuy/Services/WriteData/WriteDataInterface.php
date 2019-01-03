<?php

namespace FatchipAfterbuy\Services\WriteData;

interface WriteDataInterface {
    //TODO: params missing
    public function put(array $data);

    public function transform(array $data);

    public function send($targetData);
}
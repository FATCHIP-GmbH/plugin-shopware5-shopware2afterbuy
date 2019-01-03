<?php

namespace FatchipAfterbuy\Services\ReadData;

class AbstractReadDataService {
    protected $targetEntity;

    public function __construct(string $targetEntity) {
        $this->targetEntity = $targetEntity;
    }
}
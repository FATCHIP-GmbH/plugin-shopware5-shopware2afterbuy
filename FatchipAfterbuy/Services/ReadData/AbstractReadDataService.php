<?php

namespace FatchipAfterbuy\Services\ReadData;

use FatchipAfterbuy\ValueObjects\AbstractValueObject;

class AbstractReadDataService {
    protected $targetEntity;

    public function __construct(AbstractValueObject $targetEntity) {
        $this->targetEntity = $targetEntity;
    }
}
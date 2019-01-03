<?php

namespace FatchipAfterbuy\Services\WriteData;

use Shopware\Components\DependencyInjection\Bridge\ModelAnnotation;

class AbstractWriteDataService {

    protected $entityManager;

    protected $targetEntity;

    public function __construct(ModelAnnotation $entityManager = null, string $targetEntity = "") {
        $this->entityManager = $entityManager;
        $this->targetEntity = $targetEntity;
    }
}
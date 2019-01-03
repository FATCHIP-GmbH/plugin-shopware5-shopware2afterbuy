<?php

namespace FatchipAfterbuy\Services\WriteData;

use Shopware\Components\DependencyInjection\Bridge\ModelAnnotation;
use Shopware\Components\Model\ModelManager;

class AbstractWriteDataService {

    protected $entityManager;

    protected $targetEntity;

    public function __construct(ModelManager $entityManager = null, string $targetEntity = "") {
        $this->entityManager = $entityManager;
        $this->targetEntity = $targetEntity;
    }
}
<?php

namespace FatchipAfterbuy\Services\WriteData;

use Shopware\Components\DependencyInjection\Bridge\ModelAnnotation;
use Shopware\Components\Model\ModelManager;

class AbstractWriteDataService {

    protected $entityManager;

    public function __construct(ModelManager $entityManager = null) {
        $this->entityManager = $entityManager;
    }
}
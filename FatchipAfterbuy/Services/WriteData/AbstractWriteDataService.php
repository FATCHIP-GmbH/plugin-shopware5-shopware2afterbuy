<?php

namespace FatchipAfterbuy\Services\WriteData;

use Shopware\Components\DependencyInjection\Bridge\ModelAnnotation;
use Shopware\Components\Model\ModelManager;

class AbstractWriteDataService {

    /**
     * @var ModelManager
     */
    protected $entityManager;

    /**
     * AbstractWriteDataService constructor.
     * @param ModelManager|null $entityManager
     */
    public function __construct(ModelManager $entityManager = null) {
        $this->entityManager = $entityManager;
    }
}
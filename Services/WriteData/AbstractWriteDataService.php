<?php

namespace FatchipAfterbuy\Services\WriteData;

use Psr\Log\LoggerInterface;
use Shopware\Components\DependencyInjection\Bridge\ModelAnnotation;
use Shopware\Components\Model\ModelManager;

class AbstractWriteDataService {

    /**
     * @var ModelManager
     */
    protected $entityManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * AbstractWriteDataService constructor.
     * @param ModelManager|null $entityManager
     */
    public function __construct(ModelManager $entityManager = null) {
        $this->entityManager = $entityManager;
    }

    public function setLogger(LoggerInterface $logger) {
        $this->logger = $logger;
    }
}
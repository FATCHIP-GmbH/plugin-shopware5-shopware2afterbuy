<?php

namespace FatchipAfterbuy\Services\ReadData;

use Psr\Log\LoggerInterface;
use Shopware\Components\Model\ModelEntity;
use Shopware\Components\Model\ModelManager;

/**
 * implements methods we should use in every ReadDataService
 *
 * Class AbstractReadDataService
 * @package FatchipAfterbuy\Services\ReadData
 */
class AbstractReadDataService {
    /**
     * @var string
     */
    protected $targetEntity;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ModelEntity
     */
    protected $sourceRepository;

    protected $entityManager;

    /**
     * provides the target entity (valueObject) given via services.xml
     * !!! if different services etc are needed, we will make use of factories (symfony) !!!
     *
     * AbstractReadDataService constructor.
     * @param string $targetEntity
     * @param ModelManager $entityManager
     */
    public function __construct(string $targetEntity, ModelManager $entityManager = null) {
        $this->targetEntity = $targetEntity;
        $this->entityManager = $entityManager;
     }

    public function setLogger(LoggerInterface $logger) {
        $this->logger = $logger;
    }

    public function setRepo(string $repo) {
        $this->sourceRepository = $repo;
    }
}
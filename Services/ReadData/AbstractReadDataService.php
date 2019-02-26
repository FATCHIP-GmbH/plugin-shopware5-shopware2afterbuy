<?php

namespace viaebShopwareAfterBuy\Services\ReadData;

use viaebShopwareAfterBuy\Services\AbstractDataService;
use Psr\Log\LoggerInterface;
use Shopware\Components\Model\ModelEntity;
use Shopware\Components\Model\ModelManager;

/**
 * implements methods we should use in every ReadDataService
 *
 * Class AbstractReadDataService
 * @package viaebShopwareAfterBuy\Services\ReadData
 */
class AbstractReadDataService extends AbstractDataService {

    /**
     * @var ModelEntity
     */
    protected $sourceRepository;

    /**
     * @var string
     */
    protected $targetEntity;


    /**
     * @param string $repo
     */
    public function setRepo(string $repo) {
        $this->sourceRepository = $repo;
    }

    public function setTarget(string $target) {
        $this->targetEntity = $target;
    }
}
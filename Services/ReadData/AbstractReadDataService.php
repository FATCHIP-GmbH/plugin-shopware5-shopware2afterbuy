<?php

namespace abaccAfterbuy\Services\ReadData;

use abaccAfterbuy\Services\AbstractDataService;
use Shopware\Components\Model\ModelEntity;

/**
 * implements methods we should use in every ReadDataService
 *
 * Class AbstractReadDataService
 * @package abaccAfterbuy\Services\ReadData
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
    public function setRepo(string $repo): void
    {
        $this->sourceRepository = $repo;
    }

    public function setTarget(string $target): void
    {
        $this->targetEntity = $target;
    }
}
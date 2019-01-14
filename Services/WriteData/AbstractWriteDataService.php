<?php

namespace FatchipAfterbuy\Services\WriteData;

use FatchipAfterbuy\Services\AbstractDataService;
use Psr\Log\LoggerInterface;
use Shopware\Components\DependencyInjection\Bridge\ModelAnnotation;
use Shopware\Components\Model\ModelManager;

class AbstractWriteDataService extends AbstractDataService {

    /**
     * @var ModelEntity
     */
    protected $targetRepository;


    /**
     * @param string $repo
     */
    public function setRepo(string $repo) {
        $this->targetRepository = $repo;
    }
}
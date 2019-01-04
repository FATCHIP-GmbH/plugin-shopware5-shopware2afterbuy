<?php

namespace FatchipAfterbuy\Services\ReadData;

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
     * provides the target entity (valueObject) given via services.xml
     * !!! if different services etc are needed, we will make use of factories (symfony) !!!
     *
     * AbstractReadDataService constructor.
     * @param string $targetEntity
     */
    public function __construct(string $targetEntity) {
        $this->targetEntity = $targetEntity;
    }
}
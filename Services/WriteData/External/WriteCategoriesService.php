<?php

namespace FatchipAfterbuy\Services\WriteData\External;

use Doctrine\ORM\OptimisticLockException;
use FatchipAfterbuy\Services\Helper\AbstractHelper;
use FatchipAfterbuy\Services\Helper\ShopwareCategoryHelper;
use FatchipAfterbuy\Services\WriteData\AbstractWriteDataService;
use FatchipAfterbuy\Services\WriteData\WriteDataInterface;
use FatchipAfterbuy\ValueObjects\Category as ValueCategory;

class WriteCategoriesService extends AbstractWriteDataService implements WriteDataInterface
{

    /**
     * @var ShopwareCategoryHelper $categoryHelper
     */
    protected $categoryHelper;

    /**
     * @var string $identifier
     */
    protected $identifier;

    /**
     * @var bool $isAttribute
     */
    protected $isAttribute;

    /**
     * @param AbstractHelper $helper
     * @param string         $identifier
     * @param bool           $isAttribute
     */
    public function initHelper(AbstractHelper $helper, string $identifier, bool $isAttribute)
    {
        $this->categoryHelper = $helper;
        $this->identifier = $identifier;
        $this->isAttribute = $isAttribute;
    }

    /**
     * @param array $data
     *
     * @return mixed|void
     * @throws OptimisticLockException
     */
    public function put(array $data)
    {
        $data = $this->transform($data);

        return $this->send($data);
    }

    /**
     * transforms valueObject into final structure for storage
     * could may be moved into separate helper
     *
     * @param ValueCategory[] $data
     *
     * @return mixed|void
     */
    public function transform(array $data)
    {
        return $data;
    }


    /**
     * @param $targetData
     *
     * @return mixed|void
     * @throws OptimisticLockException
     */
    public function send($targetData)
    {
        return $targetData;
    }
}
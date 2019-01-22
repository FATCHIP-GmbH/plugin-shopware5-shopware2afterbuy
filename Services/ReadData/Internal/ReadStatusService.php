<?php

namespace FatchipAfterbuy\Services\ReadData\Internal;

use FatchipAfterbuy\Services\Helper\AbstractHelper;
use FatchipAfterbuy\Services\Helper\ShopwareCategoryHelper;
use FatchipAfterbuy\Services\Helper\ShopwareOrderHelper;
use FatchipAfterbuy\Services\ReadData\AbstractReadDataService;
use FatchipAfterbuy\Services\ReadData\ReadDataInterface;
use FatchipAfterbuy\ValueObjects\Category as ValueCategory;
use Shopware\Bundle\MediaBundle\MediaService;
use Shopware\Models\Category\Category as ShopwareCategory;

class ReadStatusService extends AbstractReadDataService implements ReadDataInterface
{

    public function get(array $filter): array
    {
        $data = $this->read($filter);

        return $this->transform($data);
    }

    public function transform(array $orders): array
    {
        if(empty($orders)) {
            return array();
        }

        $values = [];

        foreach ($orders as $order) {

        }

        return $values;
    }


    public function read(array $filter): array
    {
        /**
         * @var ShopwareOrderHelper $orderHelper
         */
        $orderHelper = $this->helper;

        return $orderHelper->getNewFullfilledOrders();
    }
}

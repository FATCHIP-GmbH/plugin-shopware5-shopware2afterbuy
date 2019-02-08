<?php

namespace FatchipAfterbuy\Services\ReadData\Internal;

use FatchipAfterbuy\Services\Helper\AbstractHelper;
use FatchipAfterbuy\Services\Helper\ShopwareCategoryHelper;
use FatchipAfterbuy\Services\Helper\ShopwareOrderHelper;
use FatchipAfterbuy\Services\ReadData\AbstractReadDataService;
use FatchipAfterbuy\Services\ReadData\ReadDataInterface;
use FatchipAfterbuy\ValueObjects\Category as ValueCategory;
use FatchipAfterbuy\ValueObjects\OrderStatus;
use Shopware\Bundle\MediaBundle\MediaService;
use Shopware\Models\Category\Category as ShopwareCategory;
use Shopware\Models\Order\Order;

class ReadStatusService extends AbstractReadDataService implements ReadDataInterface
{

    public function get(array $filter): array
    {
        $data = $this->read($filter);

        return $this->transform($data);
    }

    public function transform(array $orders): array
    {
        $this->logger->debug('Receiving updated orders from shop', $orders);

        if(empty($orders)) {
            return array();
        }

        $values = [];

        foreach ($orders as $order) {
            /**
             * @var Order $order
             */

            if(!$order->getAttribute()->getAfterbuyOrderId()) {
                continue;
            }

            $status = new OrderStatus();
            $status->setAfterbuyOrderId($order->getAttribute()->getAfterbuyOrderId());

            //should be replaced by values from status history
            $status->setPaymentDate(new \DateTime());
            $status->setShippingDate(new \DateTime());
            $status->setAmount($order->getInvoiceAmount());

            $values[] = $status;
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

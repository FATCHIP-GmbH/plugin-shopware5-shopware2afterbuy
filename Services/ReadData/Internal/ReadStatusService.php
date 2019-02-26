<?php

namespace viaebShopwareAfterBuy\Services\ReadData\Internal;

use viaebShopwareAfterBuy\Services\Helper\AbstractHelper;
use viaebShopwareAfterBuy\Services\Helper\ShopwareCategoryHelper;
use viaebShopwareAfterBuy\Services\Helper\ShopwareOrderHelper;
use viaebShopwareAfterBuy\Services\ReadData\AbstractReadDataService;
use viaebShopwareAfterBuy\Services\ReadData\ReadDataInterface;
use viaebShopwareAfterBuy\ValueObjects\Category as ValueCategory;
use viaebShopwareAfterBuy\ValueObjects\OrderStatus;
use Shopware\Bundle\MediaBundle\MediaService;
use Shopware\Models\Category\Category as ShopwareCategory;
use Shopware\Models\Order\Order;

class ReadStatusService extends AbstractReadDataService implements ReadDataInterface
{

    public function get(array $filter)
    {
        $data = $this->read($filter);

        return $this->transform($data);
    }

    public function transform(array $orders)
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


    public function read(array $filter)
    {
        /**
         * @var ShopwareOrderHelper $orderHelper
         */
        $orderHelper = $this->helper;

        return $orderHelper->getNewFullfilledOrders();
    }
}

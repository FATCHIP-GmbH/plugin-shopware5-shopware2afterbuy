<?php

namespace viaebShopwareAfterbuy\Services\ReadData\Internal;

use DateTime;
use Exception;
use viaebShopwareAfterbuy\Services\ReadData\AbstractReadDataService;
use viaebShopwareAfterbuy\Services\Helper\ShopwareOrderHelper;
use viaebShopwareAfterbuy\Services\ReadData\ReadDataInterface;
use viaebShopwareAfterbuy\ValueObjects\OrderStatus;
use Shopware\Models\Order\Order as ShopwareOrder;

class ReadStatusService extends AbstractReadDataService implements ReadDataInterface
{

    /**
     * @param array $filter
     * @return array|mixed
     */
    public function get(array $filter)
    {
        $data = $this->read($filter);

        return $this->transform($data);
    }

    /**
     * @param array $orders
     * @return array|mixed
     */
    public function transform(array $orders)
    {
        $this->logger->debug('Receiving updated orders from shop', $orders);

        if(empty($orders)) {
            return array();
        }

        $values = [];

        foreach ($orders as $shopwareOrder) {
            /** @var ShopwareOrder $shopwareOrder */
            if(!$shopwareOrder->getAttribute()->getAfterbuyOrderId()) {
                continue;
            }

            $status = new OrderStatus();
            $status->setAfterbuyOrderId($shopwareOrder->getAttribute()->getAfterbuyOrderId());

            //should be replaced by values from status history
            try {
                $status->setPaymentDate(new DateTime());
                $status->setShippingDate(new DateTime());
            }
            catch(Exception $e) {
                //ugly datetime exception handling
            }
            $status->setAmount($shopwareOrder->getInvoiceAmount());

            if ($trackingNumber = $shopwareOrder->getTrackingCode()) {
                $status->setTrackingNumber($trackingNumber);
            }

            $values[] = $status;
        }

        return $values;
    }

    /**
     * @param array $filter
     * @return mixed
     */
    public function read(array $filter)
    {
        /**
         * @var ShopwareOrderHelper $orderHelper
         */
        $orderHelper = $this->helper;

        return $orderHelper->getNewFullfilledOrders();
    }
}

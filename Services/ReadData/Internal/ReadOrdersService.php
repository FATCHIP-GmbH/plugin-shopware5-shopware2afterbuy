<?php

namespace viaebShopwareAfterbuy\Services\ReadData\Internal;

use Exception;
use viaebShopwareAfterbuy\Services\Helper\ShopwareOrderHelper;
use viaebShopwareAfterbuy\Services\ReadData\AbstractReadDataService;
use viaebShopwareAfterbuy\Services\ReadData\ReadDataInterface;
use viaebShopwareAfterbuy\ValueObjects\Order;
use Shopware\Models\Order\Repository;

class ReadOrdersService extends AbstractReadDataService implements ReadDataInterface {
    /** @var ShopwareOrderHelper */
    public $helper;

    /**
     * @param array $filter
     * @return array|null
     * @throws Exception
     */
    public function get(array $filter) {
        $data = $this->read($filter);
        return $this->transform($data);
    }

    /**
     * transforms api input into valueObject (targetEntity)
     *
     * @param array $data
     * @return array|null
     */
    public function transform(array $data) {
        $this->logger->debug('Receiving orders from shop', $data);

        if($this->targetEntity === null) {
            return null;
        }

        $targetData = array();

        foreach($data as $entity) {
            /** ignore order if not valid */
            if($entity->getBilling() === null || $entity->getDetails() === null) {
                continue;
            }

            /** @var Order $order */
            $order = new $this->targetEntity();

            //set order positions
            $positions = $this->helper->buildPositions($entity);
            $order->setPositions($positions);

            //set address related information
            $billingAddress = $this->helper->buildAddress($entity->getBilling());
            $shippingAddress = $this->helper->buildAddress($entity->getShipping());

            //set customer related data
            $this->helper->setOrderCustomerData($order, $billingAddress, $entity);

            $order->setBillingAddress($billingAddress);
            $order->setShippingAddress($shippingAddress);

            //set values
            $this->helper->setOrderValues($order, $entity);
            $this->helper->setOrderStatus($order, $entity);

            $targetData[] = $order;
        }

        return $targetData;
    }


    /**
     * provides api data. dummy data as used here can be used in tests
     *
     * @param array $filter
     * @return array
     * @throws Exception
     */
    public function read(array $filter) {

        /**
         * @var Repository $repo
         */
        $data = $this->helper->getUnexportedOrders($this->config);

        if(!$data) {
            $this->logger->error('No data received', array('Orders', 'Read', 'Internal'));
        }

        return $data;
    }
}
<?php

namespace viaebShopwareAfterbuy\Services\WriteData\Internal;

use viaebShopwareAfterbuy\Services\Helper\ShopwareOrderHelper;
use viaebShopwareAfterbuy\Services\WriteData\AbstractWriteDataService;
use viaebShopwareAfterbuy\Services\WriteData\WriteDataInterface;
use Shopware\Models\Shop\Shop;

class WriteStatusService extends AbstractWriteDataService implements WriteDataInterface {

    /**
     * @var ShopwareOrderHelper $helper
     */

    /**
     * @var array
     */
    protected $countries;

    /**
     * @var Shop
     */
    protected $targetShop;

    /** @var ShopwareOrderHelper */
    public $helper;


    /**
     * @param array $data
     * @return mixed
     */
    public function put(array $data) {
        $data = $this->transform($data);
        return $this->send($data);
    }

    /**
     * transforms valueObject into final structure for storage
     * could may be moved into separate helper
     *
     * @param array $data
     * @return mixed
     */
    public function transform(array $data) {

        foreach($data as $value) {
            $order = $this->helper->getEntity($value->getExternalIdentifier(), 'afterbuyOrderId', true);

            if($order->getId() === null) {
                continue;
            }

            if($order->getPaymentStatus()->getName() !== 'completely_paid') {
                $this->helper->setPaymentStatus($value, $order);
            }

            $this->helper->setShippingStatus($value, $order);

            $this->entityManager->persist($order);
        }

        try {
            $this->entityManager->flush();
        }
        catch(\Exception $e) {
            $this->logger->error('Error updating order state');
        }

        return $data;
    }


    /**
     * @param $targetData
     * @return mixed
     */
    public function send($targetData) {
        return array();
    }

    public function getOrdersForRequestingStatusUpdate() {

        $orders = $this->helper->getUnfullfilledOrders();

        $filterValues = [];

        foreach ($orders as $order) {
            $filterValues[] = $order['afterbuyOrderId'];
        }

        $filter = array(
            'Filter' => array(
                'FilterName' => 'OrderID',
                'FilterValues' => array (
                   'FilterValue' => $filterValues
                )
            )
        );

        return $filter;
    }
}
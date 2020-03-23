<?php
/** @noinspection SpellCheckingInspection */

namespace viaebShopwareAfterbuy\Services\WriteData\Internal;

use Exception;
use Shopware\Models\Order\Order as ShopwareOrder;
use viaebShopwareAfterbuy\Services\Helper\ShopwareOrderHelper;
use viaebShopwareAfterbuy\Services\WriteData\AbstractWriteDataService;
use viaebShopwareAfterbuy\Services\WriteData\WriteDataInterface;
use viaebShopwareAfterbuy\ValueObjects\Order as ValueOrder;

class WriteStatusService extends AbstractWriteDataService implements WriteDataInterface {

    /**
     * @var ShopwareOrderHelper $helper
     */

    /**
     * @var array
     */
    protected $countries;

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

        /** @var ValueOrder $valueOrder */
        foreach($data as $valueOrder) {
            /** @var ShopwareOrder $shopwareOrder */
            $shopwareOrder = $this->helper->getEntity($valueOrder->getExternalIdentifier(), 'afterbuyOrderId', true);

            if($shopwareOrder->getId() === null) {
                continue;
            }

            if($shopwareOrder->getPaymentStatus()->getName() !== 'completely_paid') {
                $this->helper->setPaymentStatus($valueOrder, $shopwareOrder);
            }

            $this->helper->setShippingStatus($valueOrder, $shopwareOrder);

            try {
                $this->entityManager->persist($shopwareOrder);
            }
            catch(Exception $e) {
                $this->logger->error('Error updating order state', array($shopwareOrder->getId()));
            }
        }

        try {
            $this->entityManager->flush();
        }
        catch(Exception $e) {
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
<?php

namespace FatchipAfterbuy\Services\WriteData\Internal;

use FatchipAfterbuy\Models\Status;
use FatchipAfterbuy\Services\Helper\ShopwareOrderHelper;
use FatchipAfterbuy\Services\WriteData\AbstractWriteDataService;
use FatchipAfterbuy\Services\WriteData\WriteDataInterface;
use FatchipAfterbuy\ValueObjects\Order;
use Shopware\Models\Shop\Shop;

class WriteOrdersService extends AbstractWriteDataService implements WriteDataInterface {

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

    /**
     * @param array $data
     * @return mixed|void
     * @throws \Doctrine\ORM\OptimisticLockException
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
     * @return mixed|void
     */
    public function transform(array $data) {
        $this->targetShop = $this->helper->getShop($this->config['targetShop']);
        $this->countries = $this->helper->getCountries();

        foreach($data as $value) {
            //log and ignore order if country is not setup in shop
            if(!$this->countries[strtoupper($value->getBillingAddress()->getCountry())] ) {
                $this->logger->error('Country is not available in Shop config.', array($value->getBillingAddress()->getCountry()));
                continue;
            }

            if($value->getShippingAddress() && !$this->countries[strtoupper($value->getShippingAddress()->getCountry())] ) {
                $this->logger->error('Country is not available in Shop config.', array($value->getShippingAddress()->getCountry()));
                continue;
            }

            /**
             * @var Order $value
             */

            /**
             * @var \Shopware\Models\Order\Order $order
             */
            $order = $this->helper->getEntity($value->getExternalIdentifier(), 'number', false);

            $this->helper->setOrderMainValues($value, $order, $this->targetShop);
            $this->helper->setOrderTaxValues($value, $order);

            /**
             * set payment status
             */
            $this->helper->setPaymentStatus($value, $order);

            /**
             * set shipping status
             */
            $this->helper->setShippingStatus($value, $order);

            /**
             * set payment type
             */
            $this->helper->setPaymentType($value, $order, $this->config);

            $customer = $this->helper->getCustomer($value, $value->getBillingAddress(), $this->targetShop);
            $order->setCustomer($customer);

            /**
             * set billing address
             */
            $this->helper->setAddress($value, $order, $customer);

            /**
             * set shipping address
             */
            $this->helper->setAddress($value, $order, $customer, "shipping");

            /**
             * set and update positions
             */
            $this->helper->setPositions($value, $order);

            $this->helper->setShippingType($order, $this->config["shipping"]);

            $this->entityManager->persist($order);
        }
    }


    /**
     * @param $targetData
     * @return mixed|void
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function send($targetData) {
        $this->entityManager->flush();

        $this->storeOrderImportDate();
    }

    public function storeOrderImportDate() {
        $importStatus = new Status();
        $importStatus->setLastOrderImport(new \DateTime());
        $importStatus->setId(1);

        $this->entityManager->merge($importStatus);
        $this->entityManager->flush();
    }

    public function getOrderImportDateFilter(bool $force) {

        if($force) {
            return array();
        }

        /**
         * @var $lastDate Status
         */
        $lastDate = $this->entityManager->getRepository("FatchipAfterbuy\Models\Status")->find(1);

        if(!$lastDate) {
            return array();
        }

        if(!$lastDate->getLastOrderImport()) {
            return array();
        }

        //if the shop is the data carrying system, we do only import new orders,
        //otherwise we will receive states from afterbuy for update
        if($this->config["mainSystem"] != 1) {
            $filterField = 'ModDate';
        } else {
            $filterField = 'AuctionEndDate';
        }

        $filterDate = date_format($lastDate->getLastOrderImport(), 'd.m.Y H:i:s');

        $filter = array(
            'Filter' => array(
                'FilterName' => 'DateFilter',
                'FilterValues' => array(
                    'DateFrom' => $filterDate,
                    'FilterValue' => $filterField
                )
            )
        );

        return $filter;
    }
}
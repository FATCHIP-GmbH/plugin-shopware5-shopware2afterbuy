<?php

namespace abaccAfterbuy\Services\WriteData\Internal;

use abaccAfterbuy\Models\Status;
use abaccAfterbuy\Services\Helper\ShopwareOrderHelper;
use abaccAfterbuy\Services\WriteData\AbstractWriteDataService;
use abaccAfterbuy\Services\WriteData\WriteDataInterface;
use abaccAfterbuy\ValueObjects\Order;
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
        /** @var ShopwareOrderHelper $helper */
        $helper = $this->helper;

        $this->targetShop = $helper->getShop($this->config['targetShop']);
        $this->countries = $helper->getCountries();

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

            if(!$this->config['shipping']) {
                $this->logger->error('Default shipping import type not set.');
                exit('Default shipping import type not set.');
            }

            /**
             * @var Order $value
             */

            /**
             * @var \Shopware\Models\Order\Order $order
             */
            $order = $helper->getEntity($value->getExternalIdentifier(), 'number');

            //fullfilled orders should not get updated
            if($order->getId() && $helper->isFullfilled($order)) {
                continue;
            }

            $helper->setOrderMainValues($value, $order, $this->targetShop);
            $helper->setOrderTaxValues($value, $order);

            /**
             * set payment status
             */
            $helper->setPaymentStatus($value, $order);

            /**
             * set shipping status
             */
            $helper->setShippingStatus($value, $order);

            /**
             * set payment type
             */
            $helper->setPaymentType($value, $order, $this->config);

            $customer = $helper->getCustomer($value, $value->getBillingAddress(), $this->targetShop);
            $order->setCustomer($customer);

            /**
             * set billing address
             */
            if($customer === null) {
                continue;
            }

            $helper->setAddress($value, $order, $customer);

            /**
             * set shipping address
             */
            $helper->setAddress($value, $order, $customer, 'shipping');

            /**
             * set and update positions
             */
            $helper->setPositions($value, $order);

            $helper->setShippingType($order, $this->config['shipping']);

            $this->entityManager->persist($order);
        }

        return $data;
    }


    /**
     * @param $targetData
     * @return mixed
     */
    public function send($targetData) {

        try {
            $this->entityManager->flush();

            $this->storeSubmissionDate('lastOrderImport');
        }
        catch(\Exception $e) {
            $this->logger->error($e->getMessage(), $targetData);
        }

        return array();
    }

    public function getOrderImportDateFilter(bool $force) {

        if($force) {
            return array();
        }

        /**
         * @var $lastDate Status
         */
        $lastDate = $this->entityManager->getRepository(Status::class)->find(1);

        if(!$lastDate) {
            return array();
        }

        if(!$lastDate->getLastOrderImport()) {
            return array();
        }

        //if the shop is the data carrying system, we do only import new orders,
        //otherwise we will receive states from afterbuy for update
        if($this->config['mainSystem'] != 1) {
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
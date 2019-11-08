<?php
/** @noinspection SpellCheckingInspection */

namespace viaebShopwareAfterbuy\Services\WriteData\Internal;

use Doctrine\ORM\ORMException;
use Exception;
use Shopware\Models\Order\Order;
use viaebShopwareAfterbuy\Models\Status;
use viaebShopwareAfterbuy\Services\Helper\ShopwareOrderHelper;
use viaebShopwareAfterbuy\Services\WriteData\AbstractWriteDataService;
use viaebShopwareAfterbuy\Services\WriteData\WriteDataInterface;
use Shopware\Models\Shop\Shop;

/**
 * Class WriteOrdersService
 * @package viaebShopwareAfterbuy\Services\WriteData\Internal
 * @property ShopwareOrderHelper $helper
 */
class WriteOrdersService extends AbstractWriteDataService implements WriteDataInterface {
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
        if($this->config['targetShop'] === null) {
            $this->logger->error('Target shop not defined');
            exit('Target shop not defined');
        }

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

            if(!$this->config['shipping']) {
                $this->logger->error('Default shipping import type not set.');
                exit('Default shipping import type not set.');
            }
            /** @var Order $order */
            $order = $this->helper->getEntity($value->getExternalIdentifier(), 'number');

            //fullfilled orders should not get updated
            if($order->getId() && $this->helper->isFullfilled($order)) {
                continue;
            }

            $this->helper->setOrderMainValues($value, $order, $this->targetShop);
            $this->helper->setOrderTaxValues($value, $order);
            $this->helper->setPaymentStatus($value, $order);
            $this->helper->setShippingStatus($value, $order);
            $this->helper->setPaymentType($value, $order, $this->config);

            $customer = $this->helper->getCustomer($value, $value->getBillingAddress(), $this->targetShop);
            $order->setCustomer($customer);

            if($customer === null) {
                continue;
            }

            $this->helper->setAddress($value, $order, $customer);
            $this->helper->setAddress($value, $order, $customer, 'shipping');
            $this->helper->setPositions($value, $order);
            $this->helper->setShippingType($order, $this->config['shipping']);

            try {
                $this->entityManager->persist($order);
            } catch (ORMException $e) {
                $this->logger->error('ORMException while storing order');
            }
        }

        return $data;
    }


    /**
     * @param $targetData
     * @return mixed
     */
    public function send($targetData) {
        $this->helper->resetArticleChangeTime($targetData);

        try {
            $this->entityManager->flush();

            $this->storeSubmissionDate('lastOrderImport');
        }
        catch(Exception $e) {
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
        if((int)$this->config['mainSystem'] !== 1) {
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
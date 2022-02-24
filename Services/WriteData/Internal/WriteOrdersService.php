<?php
/** @noinspection SpellCheckingInspection */

namespace viaebShopwareAfterbuy\Services\WriteData\Internal;

use Doctrine\ORM\ORMException;
use Exception;
use Shopware\Models\Order\Order as ShopwareOrder;
use viaebShopwareAfterbuy\Models\Status;
use viaebShopwareAfterbuy\Services\Helper\ShopwareOrderHelper;
use viaebShopwareAfterbuy\Services\WriteData\AbstractWriteDataService;
use viaebShopwareAfterbuy\Services\WriteData\WriteDataInterface;
use Shopware\Models\Shop\Shop;
use viaebShopwareAfterbuy\ValueObjects\Order as ValueOrder;

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

        /** @var ValueOrder $valueOrder */
        foreach($data as $valueOrder) {
            //log and ignore order if country is not setup in shop
            if(!$this->countries[strtoupper($valueOrder->getBillingAddress()->getCountry())] ) {
                $this->logger->error('Country is not available in Shop config.', array($valueOrder->getBillingAddress()->getCountry()));
                continue;
            }

            if($valueOrder->getShippingAddress() && !$this->countries[strtoupper($valueOrder->getShippingAddress()->getCountry())] ) {
                $this->logger->error('Country is not available in Shop config.', array($valueOrder->getShippingAddress()->getCountry()));
                continue;
            }

            if(!$this->config['shipping']) {
                $this->logger->error('Default shipping import type not set.');
                exit('Default shipping import type not set.');
            }
            /** @var ShopwareOrder $shopwareOrder */
            $shopwareOrder = $this->helper->getEntity($valueOrder->getExternalIdentifier(), 'number');

            //fullfilled orders should not get updated
            /* no longer needed because of next
            if($shopwareOrder->getId() && $this->helper->isFullfilled($shopwareOrder)) {
                continue;
            }
            */

            //already imported orders should not get updated
            if ($shopwareOrder->getId()) {
                continue;
            }

            $this->helper->setOrderMainValues($valueOrder, $shopwareOrder, $this->targetShop);
            $this->helper->setOrderTaxValues($valueOrder, $shopwareOrder);
            $this->helper->setPaymentStatus($valueOrder, $shopwareOrder);
            $this->helper->setShippingStatus($valueOrder, $shopwareOrder);
            $this->helper->setPaymentType($valueOrder, $shopwareOrder, $this->config);

            $customer = $this->helper->getCustomer($valueOrder, $valueOrder->getBillingAddress(), $this->targetShop);
            $shopwareOrder->setCustomer($customer);

            if($customer === null) {
                continue;
            }

            $this->helper->setAddress($valueOrder, $shopwareOrder, $customer);
            $this->helper->setAddress($valueOrder, $shopwareOrder, $customer, 'shipping');
            $this->helper->setPositions($valueOrder, $shopwareOrder);
            $this->helper->setShippingType($shopwareOrder, $this->config['shipping']);

            if ($valueOrder->getTrackingNumber()) {
                $shopwareOrder->setTrackingCode($valueOrder->getTrackingNumber());
            }

            try {
                $this->entityManager->persist($shopwareOrder);
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
        } catch (Exception $e) {
            $this->logger->error($e->getMessage(), $targetData);
        }

        return array();
    }

    /**
     * @param bool $force
     * @return array
     */
    public function getOrderImportDateFilter(bool $force) {

        if ($force) {
            return array();
        }

        /**
         * @var $lastDate Status
         */
        $lastDate = $this->entityManager->getRepository(Status::class)->find(1);

        if (!$lastDate) {
            return array();
        }

        if (!$lastDate->getLastOrderImport()) {
            return array();
        }

        //if the shop is the data carrying system, we do only import new orders,
        //otherwise we will receive states from afterbuy for update
        // if shopwarer is the data carriyng system apply the time offset config option to the date filter
        if ((int)$this->config['mainSystem'] !== 1) {
            $filterField = 'ModDate';
            $filterDate = date_format($lastDate->getLastOrderImport(), 'd.m.Y H:i:s');
        } else {
            $filterField = 'AuctionEndDate';
            $offset = is_numeric($this->config['deltaOrderDate']) ? (int)$this->config['deltaOrderDate'] : 0;
            if ($offset < 0) {
                $filterDate = date_format($lastDate->getLastOrderImport()->sub(new \DateInterval('PT' . abs($offset) . 'M')), 'd.m.Y H:i:s');
            } else {
                $filterDate = date_format($lastDate->getLastOrderImport()->add(new \DateInterval('PT' . $offset . 'M')), 'd.m.Y H:i:s');
            }
        }


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
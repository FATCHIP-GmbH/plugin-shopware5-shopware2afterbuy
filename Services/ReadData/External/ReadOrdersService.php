<?php
/** @noinspection SpellCheckingInspection */

namespace viaebShopwareAfterbuy\Services\ReadData\External;

use DateTime;
use Exception;
use Fatchip\Afterbuy\ApiClient;
use viaebShopwareAfterbuy\Components\Helper;
use viaebShopwareAfterbuy\Services\Helper\AfterbuyOrderHelper;
use viaebShopwareAfterbuy\Services\ReadData\AbstractReadDataService;
use viaebShopwareAfterbuy\Services\ReadData\ReadDataInterface;
use viaebShopwareAfterbuy\ValueObjects\Order;

/**
 * Class ReadOrdersService
 * @package viaebShopwareAfterbuy\Services\ReadData\External
 * @property AfterbuyOrderHelper $helper
 */
class ReadOrdersService extends AbstractReadDataService implements ReadDataInterface {

    /**
     * @param array $filter
     * @return array|null
     */
    public function get(array $filter) {
        $data = $this->read($filter);
        return $this->transform($data);
    }

    /**
     * transforms api input into valueObject (targetEntity)
     *
     * @param array $data
     * @return array
     */
    public function transform(array $data) {
        $this->logger->debug('Receiving orders from afterbuy', $data);

        if($this->targetEntity === null) {
            return array();
        }

        if(!array_key_exists('Orders', $data['Result'])) {
            return array();
        }
        $targetData = array();

        //handle single result
        if(array_key_exists('OrderID', $data['Result']['Orders']['Order'])) {
            $orders = $data['Result']['Orders'];
        } else {
            $orders = $data['Result']['Orders']['Order'];
        }

        foreach($orders as $entity) {
            /** @var Order $value */
            $value = new $this->targetEntity();

            $value->setExternalIdentifier($entity['OrderID']);
            $value->setAmount(Helper::convertDeString2Float($entity['PaymentInfo']['FullAmount']));

            try {
                $value->setCreateDate(new DateTime($entity['OrderDate']));
            }
            catch(Exception $e) {
                //handle annoying datetime exception
            }

            $value->setCustomerNumber('AB' . $entity['BuyerInfo']['BillingAddress']['AfterbuyUserID']);

            $this->helper->setPaymentType($value, $entity);
            $this->helper->buildOrderPositions($value, $entity);
            $this->helper->setShippingCosts($value, $entity);
            $this->helper->setShipmentStatus($value, $entity);
            $this->helper->setTransactionDetails($value, $entity);
            $this->helper->setBillingAddress($value, $entity);
            $this->helper->setShippingAddress($value, $entity);

            $value->setPaid(Helper::convertDeString2Float($entity['PaymentInfo']['AlreadyPaid']));

            if (array_key_exists('AdditionalInfo', $entity)) {
                $value->setTrackingNumber($entity['AdditionalInfo']);
            }

            $targetData[] = $value;
        }

        return $targetData;
    }

    /**
     * provides api data. dummy data as used here can be used in tests
     *
     * @param array $filter
     * @return array
     */
    public function read(array $filter) {
        $resource = new ApiClient($this->apiConfig, $this->logger);
        $data = $resource->getOrdersFromAfterbuy($filter);

        if(!$data || !$data['Result']) {
            return null;
        }

        return $data;
    }
}
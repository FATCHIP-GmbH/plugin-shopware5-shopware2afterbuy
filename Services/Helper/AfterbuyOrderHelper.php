<?php
/** @noinspection SpellCheckingInspection */

namespace viaebShopwareAfterbuy\Services\Helper;

use viaebShopwareAfterbuy\Components\Helper;
use viaebShopwareAfterbuy\ValueObjects\Address;
use viaebShopwareAfterbuy\ValueObjects\Order;
use viaebShopwareAfterbuy\ValueObjects\OrderPosition;

class AfterbuyOrderHelper extends AbstractHelper
{

    /**
     * structure differs is multiple articles per order / need to handle
     *
     * @param Order $value
     * @param array $entity
     */
    public function buildOrderPositions(Order &$value, array $entity) {
        if((int)$entity['SoldItems']['ItemsInOrder'] > 1) {
            foreach($entity['SoldItems']['SoldItem'] as $position) {
                $orderPosition = new OrderPosition();

                $orderPosition->setName($position['ItemTitle']);

                if(array_key_exists('ShopProductDetails', $position) && array_key_exists('ProductID', $position['ShopProductDetails'])) {
                    $orderPosition->setExternalIdentifier($position['ShopProductDetails']['ProductID']);
                    $orderPosition->setInternalIdentifier($position['ShopProductDetails']['EAN']);
                } else {
                    $orderPosition->setExternalIdentifier($position['ItemID']);
                }

                $orderPosition->setQuantity($position['ItemQuantity']);
                $orderPosition->setPrice(Helper::convertDeString2Float($position['ItemPrice']));
                $orderPosition->setTax(Helper::convertDeString2Float($position['TaxRate']));

                $value->getPositions()->add($orderPosition);

                if(Helper::convertDeString2Float($position['TaxRate'])) {
                    $value->addNetAmount(Helper::convertDeString2Float($position['ItemPrice']) / (1 + Helper::convertDeString2Float($position['TaxRate']) / 100), $position['ItemQuantity']);
                }
            }
        } else {
            $orderPosition = new OrderPosition();

            $orderPosition->setName($entity['SoldItems']['SoldItem']['ItemTitle']);
            $orderPosition->setPrice(Helper::convertDeString2Float($entity['SoldItems']['SoldItem']['ItemPrice']));

            if(array_key_exists('ShopProductDetails', $entity['SoldItems']['SoldItem']) && array_key_exists('ProductID', $entity['SoldItems']['SoldItem']['ShopProductDetails'])) {
                $orderPosition->setExternalIdentifier($entity['SoldItems']['SoldItem']['ShopProductDetails']['ProductID']);
            } else {
                $orderPosition->setExternalIdentifier($entity['SoldItems']['SoldItem']['ItemID']);
            }

            $orderPosition->setQuantity($entity['SoldItems']['SoldItem']['ItemQuantity']);
            $orderPosition->setTax(Helper::convertDeString2Float($entity['SoldItems']['SoldItem']['TaxRate']));

            $value->getPositions()->add($orderPosition);

            if(Helper::convertDeString2Float($entity['SoldItems']['SoldItem']['TaxRate'])) {
                $value->addNetAmount(Helper::convertDeString2Float($entity['SoldItems']['SoldItem']['ItemPrice']) / (1 + Helper::convertDeString2Float($entity['SoldItems']['SoldItem']['TaxRate']) / 100),
                    $entity['SoldItems']['SoldItem']['ItemQuantity']);
            }
        }
    }

    /**
     * @param Order $value
     * @param array $entity
     */
    public function setPaymentType(Order &$value, array $entity) {
        if(array_key_exists('PaymentID', $entity['PaymentInfo'])) {
            $value->setPaymentType($entity['PaymentInfo']['PaymentID']);
        }

        if(array_key_exists('PaymentFunction', $entity['PaymentInfo'])) {
            $value->setPaymentType($entity['PaymentInfo']['PaymentFunction']);
        }
    }

    /**
     * @param Order $value
     * @param array $entity
     */
    public function setBillingAddress(Order &$value, array $entity) {
        $billingAddress = new Address();

        $billingAddress->setFirstname($entity['BuyerInfo']['BillingAddress']['FirstName']);
        $billingAddress->setLastname($entity['BuyerInfo']['BillingAddress']['LastName']);

        if($entity['BuyerInfo']['BillingAddress']['Title'] === 'Frau') {
            $billingAddress->setSalutation('mrs');
        } else {
            $billingAddress->setSalutation('mr');
        }

        $billingAddress->setCompany($entity['BuyerInfo']['BillingAddress']['Company']);
        $billingAddress->setStreet($entity['BuyerInfo']['BillingAddress']['Street']);
        $billingAddress->setAdditionalAddressLine1($entity['BuyerInfo']['BillingAddress']['Street2']);
        $billingAddress->setZipcode($entity['BuyerInfo']['BillingAddress']['PostalCode']);
        $billingAddress->setCity($entity['BuyerInfo']['BillingAddress']['City']);
        $billingAddress->setCountry($entity['BuyerInfo']['BillingAddress']['CountryISO']);
        $billingAddress->setPhone($entity['BuyerInfo']['BillingAddress']['Phone']);
        $billingAddress->setVatId($entity['BuyerInfo']['BillingAddress']['TaxIDNumber']);
        $billingAddress->setEmail($entity['BuyerInfo']['BillingAddress']['Mail']);

        $value->setBillingAddress($billingAddress);
    }

    /**
     * @param Order $value
     * @param array $entity
     */
    public function setShippingAddress(Order &$value, array $entity) {
        if(array_key_exists('ShippingAddress', $entity['BuyerInfo']) && $entity['BuyerInfo']['ShippingAddress']['FirstName'] && !$entity['BuyerInfo']['ShippingAddress']['LastName'] &&
            $entity['BuyerInfo']['ShippingAddress']['Street'] && $entity['BuyerInfo']['ShippingAddress']['CountryISO'] && $entity['BuyerInfo']['ShippingAddress']['PostalCode']) {
            $shippingAddress = new Address();

            $shippingAddress->setFirstname($entity['BuyerInfo']['ShippingAddress']['FirstName']);
            $shippingAddress->setLastname($entity['BuyerInfo']['ShippingAddress']['LastName']);

            if(isset($entity['BuyerInfo']['ShippingAddress']['Title']) && $entity['BuyerInfo']['ShippingAddress']['Title'] === 'Frau') {
                $shippingAddress->setSalutation('mrs');
            } else {
                $shippingAddress->setSalutation('mr');
            }

            $shippingAddress->setCompany($entity['BuyerInfo']['ShippingAddress']['Company']);
            $shippingAddress->setStreet($entity['BuyerInfo']['ShippingAddress']['Street']);
            $shippingAddress->setAdditionalAddressLine1($entity['BuyerInfo']['ShippingAddress']['Street2']);
            $shippingAddress->setZipcode($entity['BuyerInfo']['ShippingAddress']['PostalCode']);
            $shippingAddress->setCity($entity['BuyerInfo']['ShippingAddress']['City']);
            $shippingAddress->setCountry($entity['BuyerInfo']['ShippingAddress']['CountryISO']);
            $shippingAddress->setPhone($entity['BuyerInfo']['ShippingAddress']['Phone']);

            $value->setShippingAddress($shippingAddress);
        }
    }

    /**
     * @param Order $value
     * @param array $entity
     */
    public function setShippingCosts(Order &$value, array $entity) {
        $shippingNet = Helper::convertDeString2Float($entity['ShippingInfo']['ShippingTotalCost']) / (1 + Helper::convertDeString2Float($entity['ShippingInfo']['ShippingTaxRate']) / 100);

        $value->setShippingNet($shippingNet);
        $value->setShipping(Helper::convertDeString2Float($entity['ShippingInfo']['ShippingTotalCost']));
        $value->setShippingTax(Helper::convertDeString2Float($entity['ShippingInfo']['ShippingTaxRate']));

        if($shippingNet) {
            $value->addNetAmount($shippingNet, 1);
        }
    }

    /**
     * @param Order $value
     * @param array $entity
     */
    public function setShipmentStatus(Order &$value, array $entity) {
        if(array_key_exists('DeliveryDate', $entity['ShippingInfo'])) {
            $value->setShipped(true);
        }
    }

    /**
     * @param Order $value
     * @param array $entity
     */
    public function setTransactionDetails(Order &$value, array $entity) {
        if(array_key_exists('PaymentTransactionID', $entity['PaymentInfo'])) {
            $value->setTransactionId($entity['PaymentInfo']['PaymentTransactionID']);
        }
    }
}
<?php

/**
 * Created by PhpStorm.
 * User: andre
 * Date: 11.09.17
 * Time: 12:22
 */
class fcafterbuyshipping
{
    public $ShippingMethod = null;
    public $ShippingCost = null;
    public $ShippingAdditionalCost = null;
    public $ShippingTotalCost = null;
    public $ShippingTaxRate = null;
    public $DeliveryDate = null;

    /**
     * Creates shipping info part of order
     *
     * @param $oXmlOrder
     * @return object
     */
    public function createShippingInfo($oXmlOrder) {
        $oXmlShippingInfo = $oXmlOrder->ShippingInfo;
        $this->ShippingMethod = (string) $oXmlShippingInfo->ShippingMethod;
        $this->ShippingCost = (string) $oXmlShippingInfo->ShippingCost;
        $this->ShippingAdditionalCost = (string) $oXmlShippingInfo->ShippingAdditionalCost;
        $this->ShippingTotalCost = (string) $oXmlShippingInfo->ShippingTotalCost;
        $this->ShippingTaxRate = (string) $oXmlShippingInfo->ShippingTaxRate;
        $this->DeliveryDate = (string) $oXmlShippingInfo->DeliveryDate;

        return $this;
    }
}
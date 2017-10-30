<?php

/**
 * Created by PhpStorm.
 * User: andre
 * Date: 11.09.17
 * Time: 11:50
 */
class fcafterbuyaddress
{
    public $AfterbuyUserID = null;
    public $AfterbuyUserIDAlt = null;
    public $UserIDPlattform = null;
    public $FirstName = null;
    public $LastName = null;
    public $Title = null;
    public $Company = null;
    public $Street = null;
    public $Street2 = null;
    public $PostalCode = null;
    public $StateOrProvince = null;
    public $City = null;
    public $Country = null;
    public $CountryISO = null;
    public $Phone = null;
    public $Fax = null;
    public $Mail = null;
    public $IsMerchant = null;
    public $TaxIDNumber = null;

    /**
     * Creates billing address from an getsolditems api call
     *
     * @param $oXmlOrder
     * @return void
     */
    public function createBillingAddressFromOrderResponse($oXmlOrder) {
        $oBillingAddress = $oXmlOrder->BuyerInfo->BillingAddress;

        $this->AfterbuyUserID = (string) $oBillingAddress->AfterbuyUserID;
        $this->AfterbuyUserIDAlt = (string) $oBillingAddress->AfterbuyUserIDAlt;
        $this->UserIDPlattform = (string) $oBillingAddress->UserIDPlattform;
        $this->Title = (string) $oBillingAddress->Title;
        $this->Phone = (string) $oBillingAddress->Phone;
        $this->Fax = (string) $oBillingAddress->Fax;
        $this->Mail = (string) $oBillingAddress->Mail;
        $this->IsMerchant = (string) $oBillingAddress->IsMerchant;
        $this->TaxIDNumber = (string) $oBillingAddress->TaxIDNumber;

        $this->FirstName = (string) $oBillingAddress->FirstName;
        $this->LastName = (string) $oBillingAddress->LastName;
        $this->Company = (string) $oBillingAddress->Company;
        $this->Street = (string) $oBillingAddress->Street;
        $this->Street2 = (string) $oBillingAddress->Street2;
        $this->PostalCode = (string) $oBillingAddress->PostalCode;
        $this->City = (string) $oBillingAddress->City;
        $this->StateOrProvince = (string) $oBillingAddress->StateOrProvince;
        $this->Country = (string) $oBillingAddress->Country;
        $this->CountryISO = (string) $oBillingAddress->CountryISO;
    }

    /**
     * Creates shipping address
     *
     * @param $oXmlOrder
     * @return void
     */
    public function createShippingAddressFromOrderResponse($oXmlOrder) {
        $oShippingAddress = $oXmlOrder->BuyerInfo->ShippingAddress;

        $this->FirstName = (string) $oShippingAddress->FirstName;
        $this->LastName = (string) $oShippingAddress->LastName;
        $this->Company = (string) $oShippingAddress->Company;
        $this->Street = (string) $oShippingAddress->Street;
        $this->Street2 = (string) $oShippingAddress->Street2;
        $this->PostalCode = (string) $oShippingAddress->PostalCode;
        $this->City = (string) $oShippingAddress->City;
        $this->StateOrProvince = (string) $oShippingAddress->StateOrProvince;
        $this->Country = (string) $oShippingAddress->Country;
        $this->CountryISO = (string) $oShippingAddress->CountryISO;
    }
}
<?php

/**
 * Class representing an afterbuy order
 * User: andre
 * Date: 11.09.17
 * Time: 11:46
 */
class fcafterbuyorder
{

    /**
     * Contains keine Ahnung
     *
     * @var string
     */
    public $InvoiceNumber = null;

    /**
     * Contains keine Ahnung
     *
     * @var string
     */
    public $OrderID = null;

    /**
     * Contains keine Ahnung
     *
     * @var string
     */
    public $EbayAccount = null;

    /**
     * Contains keine Ahnung
     *
     * @var string
     */
    public $AmazonAccount = null;

    /**
     * Contains keine Ahnung
     *
     * @var string
     */
    public $Anr = null;

    /**
     * Contains keine Ahnung
     *
     * @var string
     */
    public $AlternativeItemNumber1 = null;

    /**
     * Contains keine Ahnung
     *
     * @var string
     */
    public $FeedbackDate = null;

    /**
     * Contains keine Ahnung
     *
     * @var string
     */
    public $UserComment = null;

    /**
     * Contains keine Ahnung
     *
     * @var string
     */
    public $AdditionalInfo = null;

    /**
     * Contains keine Ahnung
     *
     * @var string
     */
    public $TrackingLink = null;

    /**
     * Contains keine Ahnung
     *
     * @var string
     */
    public $Memo = null;

    /**
     * Contains keine Ahnung
     *
     * @var string
     */
    public $InvoiceMemo = null;

    /**
     * Contains keine Ahnung
     *
     * @var string
     */
    public $FeedbackLink = null;

    /**
     * Contains keine Ahnung
     *
     * @var string
     */
    public $OrderDate = null;

    /**
     * Contains keine Ahnung
     *
     * @var string
     */
    public $OrderIDAlt = null;

    /**
     * Contains keine Ahnung
     *
     * @var fcafterbuypayment
     */
    public $PaymentInfo = null;

    /**
     * Contains keine Ahnung
     *
     * @var fcafterbuyaddress
     */
    public $BuyerInfoBilling = null;

    /**
     * Contains keine Ahnung
     *
     * @var fcafterbuyaddress
     */
    public $BuyerInfoShipping = null;

    /**
     * Contains keine Ahnung
     *
     * @var fcafterbuysolditem[]
     */
    public $SoldItems = null;

    /**
     * Contains keine Ahnung
     *
     * @var fcafterbuyshipping
     */
    public $ShippingInfo = null;


    /**
     * Method takes care of creating and returning an afterbuy order object
     *
     * @param object $oXmlOrder
     * @return void
     */
    public function createOrderByApiResponse($oXmlOrder) {
        $blValidResponse = $this->_fcValidateResponse($oXmlOrder);
        if (!$blValidResponse) return false;

        $this->_fcSetCommonValues($oXmlOrder);
        $this->_fcSetPaymentInfo($oXmlOrder);
        $this->_fcSetBuyerInfo($oXmlOrder);
        $this->_fcSetSoldItems($oXmlOrder);
        $this->_fcSetShippingInfo($oXmlOrder);
    }

    /**
     * Checks if response really contains the expected orderdata needed to build
     * order object
     *
     * @param $oXmlOrder
     * @return bool
     * @todo implementation
     */
    protected function _fcValidateResponse($oXmlOrder) {
        return true;
    }

    /**
     * Creates information for payment
     *
     * @param $oXmlOrder
     * @return object fcafterbuypayment
     */
    protected function _fcSetPaymentInfo($oXmlOrder) {
        include_once (__DIR__."/fcafterbuypayment.php");
        $oPayment = new fcafterbuypayment();
        $oPayment->createPaymentFromOrderResponse($oXmlOrder);

        $this->PaymentInfo = $oPayment;
    }

    /**
     * Creates information for buyer
     *
     * @param $oXmlOrder
     * @return void
     */
    protected function _fcSetBuyerInfo($oXmlOrder) {
        include_once (__DIR__."/fcafterbuyaddress.php");
        $oBillingAddress = new fcafterbuyaddress();
        $oShippingAddress = new fcafterbuyaddress();
        $oBillingAddress->createBillingAddressFromOrderResponse($oXmlOrder);
        $oShippingAddress->createShippingAddressFromOrderResponse($oXmlOrder);

        $this->BuyerInfoBilling = $oBillingAddress;
        $this->BuyerInfoShipping = $oShippingAddress;
    }

    /**
     * Creates sold items (order articles)
     *
     * @param $oXmlOrder
     * @return void
     */
    protected function _fcSetSoldItems($oXmlOrder) {
        include_once (__DIR__."/fcafterbuysolditem.php");

        $aSoldItems = $oXmlOrder->SoldItems;

        foreach ($aSoldItems as $oXmlSoldItem) {
            $oSoldItem = new fcafterbuysolditem();
            $oSoldItem->createSoldItemFromXml($oXmlSoldItem);
            $this->SoldItems[] = $oSoldItem;
        }
    }

    /**
     * Creates Shipping infos of orderobject
     *
     * @param $oXmlOrder
     * @return void
     */
    protected function _fcSetShippingInfo($oXmlOrder) {
        include_once (__DIR__."/fcafterbuyshipping.php");
        $oAfterbuyShipping = new fcafterbuyshipping();
        $oShippingInfo = $oAfterbuyShipping->createShippingInfo($oXmlOrder);

        $this->ShippingInfo = $oShippingInfo;
    }

    /**
     * Sets basic values of order
     *
     * @param $oXmlOrder
     * @return void
     */
    protected function _fcSetCommonValues($oXmlOrder) {
        $this->InvoiceNumber = (string) $oXmlOrder->InvoiceNumber;
        $this->OrderID = (string) $oXmlOrder->OrderID;
        $this->EbayAccount = (string) $oXmlOrder->EbayAccount;
        $this->AmazonAccount = (string) $oXmlOrder->AmazonAccount;
        $this->Anr = (string) $oXmlOrder->Anr;
        $this->AlternativeItemNumber1 = (string) $oXmlOrder->AlternativeItemNumber1;
        $this->FeedbackDate = (string) $oXmlOrder->FeedbackDate;
        $this->UserComment = (string) $oXmlOrder->UserComment;
        $this->AdditionalInfo = (string) $oXmlOrder->AdditionalInfo;
        $this->TrackingLink = (string) $oXmlOrder->TrackingLink;
        $this->UserComment = (string) $oXmlOrder->UserComment;
        $this->Memo = (string) $oXmlOrder->Memo;
        $this->InvoiceMemo = (string) $oXmlOrder->InvoiceMemo;
        $this->FeedbackLink = (string) $oXmlOrder->FeedbackLink;
        $this->OrderDate = (string) $oXmlOrder->OrderDate;
        $this->OrderIDAlt = (string) $oXmlOrder->OrderIDAlt;
    }

}
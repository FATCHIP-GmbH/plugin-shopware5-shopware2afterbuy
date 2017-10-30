<?php

/**
 * Created by PhpStorm.
 * User: andre
 * Date: 11.09.17
 * Time: 12:08
 */
class fcafterbuysolditem
{
    public $ItemDetailsDone = null;
    public $ItemID = null;
    public $Anr = null;
    public $AmazonAccount = null;
    public $IsAmazonPrime = null;
    public $FulfillmentServiceLevel = null;
    public $eBayTransactionID = null;
    public $AlternativeItemNumber1 = null;
    public $AlternativeItemNumber = null;
    public $InternalItemType = null;
    public $UserDefinedFlag = null;
    public $ItemTitle = null;
    public $ItemQuantity = null;
    public $ItemPrice = null;
    public $ItemEndDate = null;
    public $TaxRate = null;
    public $ItemWeight = null;
    public $ItemXmlDate = null;
    public $ItemModDate = null;
    public $ItemPlatformName = null;
    public $ItemLink = null;
    public $eBayFeedbackCompleted = null;
    public $eBayFeedbackReceived = null;
    public $eBayFeedbackCommentType = null;
    public $ShopProductDetails = null;
    public $SoldItemAttributes = null;

    /**
     * Creates sold item
     *
     * @param simplexml object
     * @return void
     */
    public function createSoldItemFromXmlSoldItem($oXmlSoldItem) {
        $oXmlSoldItem = $oXmlSoldItem->SoldItem;
        $this->ItemDetailsDone = (string) $oXmlSoldItem->ItemDetailsDone;
        $this->ItemID = (string) $oXmlSoldItem->ItemID;
        $this->Anr = (string) $oXmlSoldItem->Anr;
        $this->IsAmazonBusiness = (string) $oXmlSoldItem->IsAmazonBusiness;
        $this->IsAmazonPrime = (string) $oXmlSoldItem->IsAmazonPrime;
        $this->FulfillmentServiceLevel = (string) $oXmlSoldItem->FulfillmentServiceLevel;
        $this->eBayTransactionID = (string) $oXmlSoldItem->eBayTransactionID;
        $this->AlternativeItemNumber1 = (string) $oXmlSoldItem->AlternativeItemNumber1;
        $this->AlternativeItemNumber = (string) $oXmlSoldItem->AlternativeItemNumber;
        $this->InternalItemType = (string) $oXmlSoldItem->InternalItemType;
        $this->UserDefinedFlag = (string) $oXmlSoldItem->UserDefinedFlag;
        $this->ItemTitle = (string) $oXmlSoldItem->ItemTitle;
        $this->ItemQuantity = (string) $oXmlSoldItem->ItemQuantity;
        $this->ItemPrice = (string) $oXmlSoldItem->ItemPrice;
        $this->ItemEndDate = (string) $oXmlSoldItem->ItemEndDate;
        $this->TaxRate = (string) $oXmlSoldItem->TaxRate;
        $this->ItemWeight = (string) $oXmlSoldItem->ItemWeight;
        $this->ItemXmlDate = (string) $oXmlSoldItem->ItemXmlDate;
        $this->ItemModDate = (string) $oXmlSoldItem->ItemModDate;
        $this->ItemPlatformName = (string) $oXmlSoldItem->ItemPlatformName;
        $this->ItemLink = (string) $oXmlSoldItem->ItemLink;
        $this->eBayFeedbackCompleted = (string) $oXmlSoldItem->eBayFeedbackCompleted;
        $this->eBayFeedbackReceived = (string) $oXmlSoldItem->eBayFeedbackReceived;
        $this->eBayFeedbackCommentType = (string) $oXmlSoldItem->eBayFeedbackCommentType;
        $this->ShopProductDetails = $this->_fcGetShopProductDetails($oXmlSoldItem);
        $this->SoldItemAttributes = $this->_fcGetItemAttributes($oXmlSoldItem);
    }

    /**
     * Creates needed product details data
     *
     * @param object $oXmlSoldItem
     * @return object
     */
    protected function _fcGetShopProductDetails($oXmlSoldItem) {
        $oShopProductDetails = new stdClass();

        $oShopProductDetails->ProductID = (string) $oXmlSoldItem->ShopProductDetails->ProductID;
        $oShopProductDetails->EAN = (string) $oXmlSoldItem->ShopProductDetails->EAN;
        $oShopProductDetails->Anr = (string) $oXmlSoldItem->ShopProductDetails->Anr;
        $oShopProductDetails->UnitOfQuantity = (string) $oXmlSoldItem->ShopProductDetails->UnitOfQuantity;
        $oShopProductDetails->BasepriceFactor = (string) $oXmlSoldItem->ShopProductDetails->BasepriceFactor;
        $oShopProductDetails->BaseProductDetails = $this->_fcGetBaseProductData($oXmlSoldItem);

        return $oShopProductDetails;
    }

    /**
     * Creates subset of product base data
     *
     * @param $oXmlSoldItem
     * @return object
     */
    protected function _fcGetBaseProductData($oXmlSoldItem) {
        $oBaseProductData = new stdClass();
        $oChildProduct = new stdClass();
        $oBaseProductData->BaseProductType = (string) $oXmlSoldItem->ShopProductDetails->BaseProductData->BaseProductType;

        if (isset($oXmlSoldItem->ShopProductDetails->BaseProductData->ChildProduct)) {
            $oXmlChildProduct = $oXmlSoldItem->ShopProductDetails->BaseProductData->ChildProduct;
            $oChildProduct->ProductID = (string) $oXmlChildProduct->ProductID;
            $oChildProduct->ProductEAN = (string) $oXmlChildProduct->ProductEAN;
            $oChildProduct->ProductANr = (string) $oXmlChildProduct->ProductANr;
            $oChildProduct->ProductName = (string) $oXmlChildProduct->ProductName;
            $oChildProduct->ProductQuantity = (string) $oXmlChildProduct->ProductQuantity;
            $oChildProduct->ProductVAT = (string) $oXmlChildProduct->ProductVAT;
            $oChildProduct->ProductWeight = (string) $oXmlChildProduct->ProductWeight;
            $oChildProduct->ProductUnitPrice = (string) $oXmlChildProduct->ProductUnitPrice;
        }

        $oBaseProductData->ChildProduct = $oChildProduct;

        return $oBaseProductData;
    }

    /**
     * Creates subset of article attributes
     *
     * @param object $oXmlSoldItem
     * @return array
     */
    protected function _fcGetItemAttributes($oXmlSoldItem) {
        $oXmlItemAttributes = $oXmlSoldItem->SoldItemAttributes;
        $aItemAttributes = array();

        foreach ($oXmlItemAttributes as $oXmlItemAttribute) {
            $oItemAttribute = new stdClass();
            $oItemAttribute->AttributeName = (string) $oXmlItemAttribute->AttributeName;
            $oItemAttribute->AttributeValue = (string) $oXmlItemAttribute->AttributeValue;
            $oItemAttribute->AttributePosition = (string) $oXmlItemAttribute->AttributePosition;

            $aItemAttributes[] = $oItemAttribute;
        }

        return $aItemAttributes;
    }

}
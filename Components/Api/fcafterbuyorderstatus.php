<?php
/**
 * Created by PhpStorm.
 * User: Hendrik
 * Date: 04.09.2017
 * Time: 23:03
 */

class fcafterbuyorderstatus
{
    public $OrderID = null;
    public $ItemID = null;
    public $UserDefinedFlag = null;
    public $AdditionalInfo = null;
    public $MailDate = null;
    public $ReminderMailDate = null;
    public $UserComment = null;
    public $OrderMemo = null;
    public $InvoiceMemo = null;
    public $InvoiceNumber = null;
    public $OrderExported = null;
    public $InvoiceDate = null;
    public $HideOrder = null;
    public $Reminder1Date = null;
    public $Reminder2Date = null;
    public $FeedbackDate = null;
    public $XmlDate = null;
    public $BuyerInfo = null;
    public $PaymentInfo = null;
    public $ShippingInfo = null;
    public $VorgangsInfo  = null;

    /**
     * Creates an afterbuy status object by delivered data. Array should be assoc and
     * contain index names equal to public attributes of this object
     *
     * @param $aData
     */
    public function createStatusObjectByData($Data) {
        foreach ($Data as $AttributeName=>$Value) {
            if (isset($this->$AttributeName)) {
                $this->$AttributeName = $Value;
            }
        }
    }
}

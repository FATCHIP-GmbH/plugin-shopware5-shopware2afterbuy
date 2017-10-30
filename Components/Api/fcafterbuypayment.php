<?php

/**
 * Created by PhpStorm.
 * User: andre
 * Date: 11.09.17
 * Time: 11:51
 */
class fcafterbuypayment
{
    public $PaymentID = null;
    public $PaymentMethod = null;
    public $PaymentFunction = null;
    public $PaymentData = array();
    public $PaymentTransactionID = null;
    public $PaymentStatus = null;
    public $PaymentDate = null;
    public $AlreadyPaid = null;
    public $FullAmount = null;
    public $PaymentInstruction = null;
    public $InvoiceDate = null;

    /**
     * Creates payment information node of an afterbuy order resultset
     *
     * @param $oXmlOrder
     * @return void
     */
    public function createPaymentFromOrderResponse($oXmlOrder) {
        $oPaymentInfo = $oXmlOrder->PaymentInfo;

        $this->PaymentID = (string) $oPaymentInfo->PaymentID;
        $this->PaymentMethod = (string) $oPaymentInfo->PaymentMethod;
        $this->PaymentFunction = (string) $oPaymentInfo->PaymentFunction;
        $this->PaymentData['BankCode'] = (string) $oPaymentInfo->PaymentData->BankCode;
        $this->PaymentData['AccountHolder'] = (string) $oPaymentInfo->PaymentData->AccountHolder;
        $this->PaymentData['BankName'] = (string) $oPaymentInfo->PaymentData->BankName;
        $this->PaymentData['AccountNumber'] = (string) $oPaymentInfo->PaymentData->AccountNumber;
        $this->PaymentData['Iban'] = (string) $oPaymentInfo->PaymentData->Iban;
        $this->PaymentData['Bic'] = (string) $oPaymentInfo->PaymentData->Bic;
        $this->PaymentData['ReferenceNumber'] = (string) $oPaymentInfo->PaymentData->ReferenceNumber;
        $this->PaymentTransactionID = (string) $oPaymentInfo->PaymentTransactionID;
        $this->PaymentStatus = (string) $oPaymentInfo->PaymentStatus;
        $this->PaymentDate = (string) $oPaymentInfo->PaymentDate;
        $this->AlreadyPaid = (string) $oPaymentInfo->AlreadyPaid;
        $this->FullAmount = (string) $oPaymentInfo->FullAmount;
        $this->PaymentInstruction = (string) $oPaymentInfo->PaymentInstruction;
        $this->InvoiceDate = (string) $oPaymentInfo->InvoiceDate;
        $this->EFTID = (string) $oPaymentInfo->EFTID;
    }
}
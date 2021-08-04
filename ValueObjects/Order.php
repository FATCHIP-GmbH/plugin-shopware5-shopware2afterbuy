<?php
/** @noinspection SpellCheckingInspection */

namespace viaebShopwareAfterbuy\ValueObjects;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use viaebShopwareAfterbuy\ValueObjects\Address as AddressAlias;

class Order extends AbstractValueObject {

    /**
     * we cannot define external identifier types, we have to handle those as strings
     *
     * @var string $externalIdentifier
     */
    public $externalIdentifier;

    /**
     * integer works with category ids, articles use strings (ordernumber)
     *
     * @var string $internalIdentifier
     */
    public $internalIdentifier;

    /**
     * @var ArrayCollection
     */
    public $positions;

    /**
     * @var AddressAlias
     */
    public $shippingAddress;

    /**
     * @var AddressAlias
     */
    public $billingAddress;

    /**
     * @var float
     */
    public $amount;

    /**
     * @var float
     */
    public $shipping;

    /**
     * @var float
     */
    public $amountNet = 0;

    /**
     * @var float
     */
    public $paid;

    /**
     * @var string
     */
    public $currency = 'EUR';

    /**
     * @var string
     */
    public $transactionId = '';

    /**
     * @var string
     */
    public $customergroup = '';

    /**
     * @var bool
     */
    public $taxFree = false;

    /**
     * @var bool
     */
    public $net = false;

    /**
     * @var float
     */
    public $shippingNet;

    /**
     * @var float
     */
    public $shippingTax;

    /**
     * @var bool
     */
    public $shipped = false;

    /**
     * @var DateTime
     */
    public $createDate;

    /**
     * @var DateTime
     */
    public $updateDate;

    public $paymentType = 'OTHERS';

    public $customerNumber;

    public $shippingType = 'Standard';

    /**
     * @var bool
     */
    public $cleared = false;

    /**
     * @var int
     */
    public $paymentTypeId;

    /**
     * @var string
     */
    public $trackingNumber;

    /**
     * @var string
     */
    public $paymentStatus;

    /**
     * @return bool
     */
    public function isCleared()
    {
        return $this->cleared;
    }

    /**
     * @param bool $cleared
     */
    public function setCleared(bool $cleared)
    {
        $this->cleared = $cleared;
    }

    /**
     * @noinspection PhpUnused
     * @return int
     */
    public function getPaymentTypeId()
    {
        return $this->paymentTypeId;
    }

    /**
     * @param int $paymentTypeId
     */
    public function setPaymentTypeId(int $paymentTypeId)
    {
        $this->paymentTypeId = $paymentTypeId;
    }



    /**
     * @return string
     */
    public function getShippingType()
    {
        return $this->shippingType;
    }

    /**
     * @param string $shippingType
     */
    public function setShippingType(string $shippingType)
    {
        $this->shippingType = $shippingType;
    }



    /**
     * @return mixed
     */
    public function getCustomerNumber()
    {
        return $this->customerNumber;
    }

    /**
     * @param mixed $customerNumber
     */
    public function setCustomerNumber($customerNumber)
    {
        $this->customerNumber = $customerNumber;
    }

    public function addNetAmount(float $value, int $quantity)
    {
        $this->amountNet += $value * $quantity;
    }

    /**
     * @return string
     */
    public function getPaymentType()
    {
        return $this->paymentType;
    }

    /**
     * @param string $paymentType
     */
    public function setPaymentType(string $paymentType)
    {
        $this->paymentType = $paymentType;
    }


    public function __construct() {
        $this->positions = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getExternalIdentifier()
    {
        return $this->externalIdentifier;
    }

    /**
     * @param string $externalIdentifier
     */
    public function setExternalIdentifier(string $externalIdentifier)
    {
        $this->externalIdentifier = $externalIdentifier;
    }

    /**
     * @return int
     */
    public function getInternalIdentifier()
    {
        return $this->internalIdentifier;
    }

    /**
     * @param int $internalIdentifier
     */
    public function setInternalIdentifier(int $internalIdentifier)
    {
        $this->internalIdentifier = $internalIdentifier;
    }

    /**
     * @return ArrayCollection
     */
    public function getPositions()
    {
        return $this->positions;
    }

    /**
     * @param ArrayCollection $positions
     */
    public function setPositions(ArrayCollection $positions)
    {
        $this->positions = $positions;
    }

    /**
     * @return Address
     */
    public function getShippingAddress()
    {
        return $this->shippingAddress;
    }

    /**
     * @param Address $shippingAddress
     */
    public function setShippingAddress(Address $shippingAddress)
    {
        $this->shippingAddress = $shippingAddress;
    }

    /**
     * @return Address
     */
    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    /**
     * @param Address $billingAddress
     */
    public function setBillingAddress(Address $billingAddress)
    {
        $this->billingAddress = $billingAddress;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     */
    public function setAmount(float $amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return float
     */
    public function getShipping()
    {
        return $this->shipping;
    }

    /**
     * @param float $shipping
     */
    public function setShipping(float $shipping)
    {
        $this->shipping = $shipping;
    }

    /**
     * @return float
     */
    public function getAmountNet()
    {
        return $this->amountNet;
    }

    /**
     * @noinspection PhpUnused
     * @param float $amountNet
     */
    public function setAmountNet(float $amountNet)
    {
        $this->amountNet = $amountNet;
    }

    /**
     * @return float
     */
    public function getPaid()
    {
        return $this->paid;
    }

    /**
     * @param float $paid
     */
    public function setPaid(float $paid)
    {
        $this->paid = $paid;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     */
    public function setCurrency(string $currency)
    {
        $this->currency = $currency;
    }

    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @param string $transactionId
     */
    public function setTransactionId(string $transactionId)
    {
        $this->transactionId = $transactionId;
    }

    /**
     * @return string
     */
    public function getCustomerGroup()
    {
        return $this->customergroup;
    }

    /**
     * @param string $customergroup
     */
    public function setCustomerGroup(string $customergroup)
    {
        $this->customergroup = $customergroup;
    }

    /**
     * @return string
     */
    public function getPaymentStatus()
    {
        return $this->paymentStatus;
    }

    /**
     * @param string $paymentStatus
     */
    public function setPaymentStatus(string $paymentStatus)
    {
        $this->paymentStatus = $paymentStatus;
    }

    /**
     * @return bool
     */
    public function isTaxFree()
    {
        return $this->taxFree;
    }

    /**
     * @param bool $taxFree
     */
    public function setTaxFree(bool $taxFree)
    {
        $this->taxFree = $taxFree;
    }

    /**
     * @return bool
     */
    public function isNet()
    {
        return $this->net;
    }

    /**
     * @param bool $net
     */
    public function setNet(bool $net)
    {
        $this->net = $net;
    }

    /**
     * @return float
     */
    public function getShippingNet()
    {
        return $this->shippingNet;
    }

    /**
     * @param float $shippingNet
     */
    public function setShippingNet(float $shippingNet)
    {
        $this->shippingNet = $shippingNet;
    }

    /**
     * @return float
     */
    public function getShippingTax()
    {
        return $this->shippingTax;
    }

    /**
     * @param float $shippingTax
     */
    public function setShippingTax(float $shippingTax)
    {
        $this->shippingTax = $shippingTax;
    }

    /**
     * @return bool
     */
    public function isShipped()
    {
        return $this->shipped;
    }

    /**
     * @param bool $shipped
     */
    public function setShipped(bool $shipped)
    {
        $this->shipped = $shipped;
    }

    /**
     * @return DateTime
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * @param DateTime $createDate
     */
    public function setCreateDate(DateTime $createDate)
    {
        $this->createDate = $createDate;
    }

    /**
     * @noinspection PhpUnused
     * @return DateTime
     */
    public function getUpdateDate()
    {
        return $this->updateDate;
    }

    /**
     * @noinspection PhpUnused
     * @param DateTime $updateDate
     */
    public function setUpdateDate(DateTime $updateDate)
    {
        $this->updateDate = $updateDate;
    }

    /**
     * @return string
     */
    public function getTrackingNumber()
    {
        return $this->trackingNumber;
    }

    /**
     * @param string $trackingNumber
     */
    public function setTrackingNumber($trackingNumber)
    {
        $this->trackingNumber = $trackingNumber;
    }
}
<?php

namespace FatchipAfterbuy\ValueObjects;

use Doctrine\Common\Collections\ArrayCollection;
use FatchipAfterbuy\ValueObjects\Address as AddressAlias;

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
    public $transactionId = "";

    /**
     * @var bool
     */
    public $taxFree = false;

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
     * @var \DateTime
     */
    public $createDate;

    /**
     * @var \DateTime
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
     * @return bool
     */
    public function isCleared(): bool
    {
        return $this->cleared;
    }

    /**
     * @param bool $cleared
     */
    public function setCleared(bool $cleared): void
    {
        $this->cleared = $cleared;
    }

    /**
     * @return int
     */
    public function getPaymentTypeId(): int
    {
        return $this->paymentTypeId;
    }

    /**
     * @param int $paymentTypeId
     */
    public function setPaymentTypeId(int $paymentTypeId): void
    {
        $this->paymentTypeId = $paymentTypeId;
    }



    /**
     * @return string
     */
    public function getShippingType(): string
    {
        return $this->shippingType;
    }

    /**
     * @param string $shippingType
     */
    public function setShippingType(string $shippingType): void
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
    public function setCustomerNumber($customerNumber): void
    {
        $this->customerNumber = $customerNumber;
    }

    public function addNetAmount(float $value, int $quantity) {
        $this->amountNet += $value * $quantity;
    }

    /**
     * @return string
     */
    public function getPaymentType(): string
    {
        return $this->paymentType;
    }

    /**
     * @param string $paymentType
     */
    public function setPaymentType(string $paymentType): void
    {
        $this->paymentType = $paymentType;
    }


    public function __construct() {
        $this->positions = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getExternalIdentifier(): string
    {
        return $this->externalIdentifier;
    }

    /**
     * @param string $externalIdentifier
     */
    public function setExternalIdentifier(string $externalIdentifier): void
    {
        $this->externalIdentifier = $externalIdentifier;
    }

    /**
     * @return int
     */
    public function getInternalIdentifier(): int
    {
        return $this->internalIdentifier;
    }

    /**
     * @param int $internalIdentifier
     */
    public function setInternalIdentifier(int $internalIdentifier): void
    {
        $this->internalIdentifier = $internalIdentifier;
    }

    /**
     * @return ArrayCollection
     */
    public function getPositions(): ArrayCollection
    {
        return $this->positions;
    }

    /**
     * @param ArrayCollection $positions
     */
    public function setPositions(ArrayCollection $positions): void
    {
        $this->positions = $positions;
    }

    /**
     * @return Address
     */
    public function getShippingAddress(): ?Address
    {
        return $this->shippingAddress;
    }

    /**
     * @param Address $shippingAddress
     */
    public function setShippingAddress(Address $shippingAddress): void
    {
        $this->shippingAddress = $shippingAddress;
    }

    /**
     * @return Address
     */
    public function getBillingAddress(): Address
    {
        return $this->billingAddress;
    }

    /**
     * @param Address $billingAddress
     */
    public function setBillingAddress(Address $billingAddress): void
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
    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return float
     */
    public function getShipping(): float
    {
        return $this->shipping;
    }

    /**
     * @param float $shipping
     */
    public function setShipping(float $shipping): void
    {
        $this->shipping = $shipping;
    }

    /**
     * @return float
     */
    public function getAmountNet(): float
    {
        return $this->amountNet;
    }

    /**
     * @param float $amountNet
     */
    public function setAmountNet(float $amountNet): void
    {
        $this->amountNet = $amountNet;
    }

    /**
     * @return float
     */
    public function getPaid(): float
    {
        return $this->paid;
    }

    /**
     * @param float $paid
     */
    public function setPaid(float $paid): void
    {
        $this->paid = $paid;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     */
    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    /**
     * @return string
     */
    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    /**
     * @param string $transactionId
     */
    public function setTransactionId(string $transactionId): void
    {
        $this->transactionId = $transactionId;
    }

    /**
     * @return bool
     */
    public function isTaxFree(): bool
    {
        return $this->taxFree;
    }

    /**
     * @param bool $taxFree
     */
    public function setTaxFree(bool $taxFree): void
    {
        $this->taxFree = $taxFree;
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
    public function setShippingNet(float $shippingNet): void
    {
        $this->shippingNet = $shippingNet;
    }

    /**
     * @return float
     */
    public function getShippingTax(): float
    {
        return $this->shippingTax;
    }

    /**
     * @param float $shippingTax
     */
    public function setShippingTax(float $shippingTax): void
    {
        $this->shippingTax = $shippingTax;
    }

    /**
     * @return bool
     */
    public function isShipped(): bool
    {
        return $this->shipped;
    }

    /**
     * @param bool $shipped
     */
    public function setShipped(bool $shipped): void
    {
        $this->shipped = $shipped;
    }

    /**
     * @return \DateTime
     */
    public function getCreateDate(): \DateTime
    {
        return $this->createDate;
    }

    /**
     * @param \DateTime $createDate
     */
    public function setCreateDate(\DateTime $createDate): void
    {
        $this->createDate = $createDate;
    }

    /**
     * @return \DateTime
     */
    public function getUpdateDate(): \DateTime
    {
        return $this->updateDate;
    }

    /**
     * @param \DateTime $updateDate
     */
    public function setUpdateDate(\DateTime $updateDate): void
    {
        $this->updateDate = $updateDate;
    }





}
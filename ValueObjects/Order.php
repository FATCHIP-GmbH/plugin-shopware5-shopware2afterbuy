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
     * @var int $internalIdentifier
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
     * @var
     */
    public $amount;

    /*    public $paymentType;

    public $paymentStatus;

    public $shippingType;

    public $shippingStatus;*/

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
    public function getShippingAddress(): Address
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
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     */
    public function setAmount($amount): void
    {
        $this->amount = $amount;
    }
}
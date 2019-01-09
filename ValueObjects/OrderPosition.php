<?php

namespace FatchipAfterbuy\ValueObjects;

use FatchipAfterbuy\ValueObjects\Address as AddressAlias;

class OrderPosition extends AbstractValueObject {

    /**
     * @var string
     */
    public $internalIdentifier;
    /**
     * @var string
     */
    public $externalIdentifier;

    /**
     * @var float
     */
    public $price;

    /**
     * @var string
     */
    public $orderId;

    /**
     * @var string
     */
    public $name;

    public $tax;

    public $quantity;


    /**
     * @return string
     */
    public function getPrice(): string
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice(string $price): void
    {
        $this->price = $price;
    }

    /**
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }

    /**
     * @param string $orderId
     */
    public function setOrderId(string $orderId): void
    {
        $this->orderId = $orderId;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getInternalIdentifier(): string
    {
        return $this->internalIdentifier;
    }

    /**
     * @param string $internalIdentifier
     */
    public function setInternalIdentifier(string $internalIdentifier): void
    {
        $this->internalIdentifier = $internalIdentifier;
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
     * @return mixed
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * @param mixed $tax
     */
    public function setTax($tax): void
    {
        $this->tax = $tax;
    }

    /**
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param mixed $quantity
     */
    public function setQuantity($quantity): void
    {
        $this->quantity = $quantity;
    }

}
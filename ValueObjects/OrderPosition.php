<?php

namespace FatchipAfterbuy\ValueObjects;

use FatchipAfterbuy\ValueObjects\Address as AddressAlias;

class OrderPosition extends AbstractValueObject {

    //TODO: add shipping costs
    /**
     * @var string
     */
    public $articleId;

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

    /**
     * @return string
     */
    public function getArticleId(): string
    {
        return $this->articleId;
    }

    /**
     * @param string $articleId
     */
    public function setArticleId(string $articleId): void
    {
        $this->articleId = $articleId;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice(float $price): void
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
     * @return AddressAlias
     */
    public function getShippingAddress(): AddressAlias
    {
        return $this->shippingAddress;
    }

    /**
     * @param AddressAlias $shippingAddress
     */
    public function setShippingAddress(AddressAlias $shippingAddress): void
    {
        $this->shippingAddress = $shippingAddress;
    }

    /**
     * @return AddressAlias
     */
    public function getBillingAddress(): AddressAlias
    {
        return $this->billingAddress;
    }

    /**
     * @param AddressAlias $billingAddress
     */
    public function setBillingAddress(AddressAlias $billingAddress): void
    {
        $this->billingAddress = $billingAddress;
    }

/*    public $paymentType;

    public $paymentStatus;

    public $shippingType;

    public $shippingStatus;*/


}
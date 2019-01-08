<?php

namespace FatchipAfterbuy\ValueObjects;

use Shopware\Models\Customer\Address;

class OrderPosition extends AbstractValueObject {

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
    public $oderId;

    /**
     * @var string
     */
    public $name;

    //TODO: use different adress valueObject
    /**
     * @var Address
     */
    public $shippingAddress;

    /**
     * @var Address
     */
    public $billingAddress;

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
    public function getOderId(): string
    {
        return $this->oderId;
    }

    /**
     * @param string $oderId
     */
    public function setOderId(string $oderId): void
    {
        $this->oderId = $oderId;
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

/*    public $paymentType;

    public $paymentStatus;

    public $shippingType;

    public $shippingStatus;*/


}
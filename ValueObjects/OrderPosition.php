<?php

namespace viaebShopwareAfterbuy\ValueObjects;

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
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice(float $price)
    {
        $this->price = $price;
    }

    /**
     * @return string
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @param string $orderId
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getInternalIdentifier()
    {
        return $this->internalIdentifier;
    }

    /**
     * @param string $internalIdentifier
     */
    public function setInternalIdentifier($internalIdentifier)
    {
        $this->internalIdentifier = $internalIdentifier;
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
    public function setExternalIdentifier($externalIdentifier)
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
    public function setTax($tax)
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
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

}
<?php

namespace Fatchip\Afterbuy\Types\Product;

class ScaledDiscount
{
    /** @var int */
    private $ScaledQuantity;
    /** @var float */
    private $ScaledPrice;
    /** @var float */
    private $ScaledDPrice;

    /**
     * @return int
     */
    public function getScaledQuantity()
    {
        return $this->ScaledQuantity;
    }

    /**
     * @param int $ScaledQuantity
     * @return ScaledDiscount
     */
    public function setScaledQuantity($ScaledQuantity)
    {
        $this->ScaledQuantity = $ScaledQuantity;
        return $this;
    }

    /**
     * @return float
     */
    public function getScaledPrice()
    {
        return $this->ScaledPrice;
    }

    /**
     * @param float $ScaledPrice
     * @return ScaledDiscount
     */
    public function setScaledPrice($ScaledPrice)
    {
        $this->ScaledPrice = $ScaledPrice;
        return $this;
    }

    /**
     * @return float
     */
    public function getScaledDPrice()
    {
        return $this->ScaledDPrice;
    }

    /**
     * @param float $ScaledDPrice
     * @return ScaledDiscount
     */
    public function setScaledDPrice($ScaledDPrice)
    {
        $this->ScaledDPrice = $ScaledDPrice;
        return $this;
    }
}

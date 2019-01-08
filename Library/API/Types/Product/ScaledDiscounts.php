<?php

namespace Fatchip\Afterbuy\Types\Product;

class ScaledDiscounts
{
    /** @var ScaledDiscount[] */
    private $ScaledDiscount;

    /**
     * @return ScaledDiscount[]
     */
    public function getScaledDiscount()
    {
        return $this->ScaledDiscount;
    }

    /**
     * @param ScaledDiscount[] $ScaledDiscount
     * @return ScaledDiscounts
     */
    public function setScaledDiscount($ScaledDiscount)
    {
        $this->ScaledDiscount = $ScaledDiscount;
        return $this;
    }
}

<?php

namespace Fatchip\Afterbuy\Types\Product;

class AddBaseProduct
{
    const VARIANT_OPTIONAL = 0;
    const VARIANT_STANDARD = 1;

    /** @var int */
    private $ProductID;
    /** @var string */
    private $ProductLabel;
    /** @var int */
    private $ProductPos;
    /** @var int */
    private $DefaultProduct;
    /** @var int */
    private $ProductQuantity;

    /**
     * AddBaseProduct constructor.
     * @param int $ProductID
     * @param string $ProductLabel
     * @param int $DefaultProduct
     */
    public function __construct($ProductID, $ProductLabel = null, $DefaultProduct = null)
    {
        $this->ProductID = $ProductID;
        $this->ProductLabel = $ProductLabel;
        $this->DefaultProduct = $DefaultProduct;
    }

    /**
     * @return int
     */
    public function getProductID()
    {
        return $this->ProductID;
    }

    /**
     * @param int $ProductID
     * @return AddBaseProduct
     */
    public function setProductID($ProductID)
    {
        $this->ProductID = $ProductID;
        return $this;
    }

    /**
     * @return string
     */
    public function getProductLabel()
    {
        return $this->ProductLabel;
    }

    /**
     * @param string $ProductLabel
     * @return AddBaseProduct
     */
    public function setProductLabel($ProductLabel)
    {
        $this->ProductLabel = $ProductLabel;
        return $this;
    }

    /**
     * @return int
     */
    public function getProductPos()
    {
        return $this->ProductPos;
    }

    /**
     * @param int $ProductPos
     * @return AddBaseProduct
     */
    public function setProductPos($ProductPos)
    {
        $this->ProductPos = $ProductPos;
        return $this;
    }

    /**
     * @return int
     */
    public function getDefaultProduct()
    {
        return $this->DefaultProduct;
    }

    /**
     * @param int $DefaultProduct
     * @return AddBaseProduct
     */
    public function setDefaultProduct($DefaultProduct)
    {
        $this->DefaultProduct = $DefaultProduct;
        return $this;
    }

    /**
     * @return int
     */
    public function getProductQuantity()
    {
        return $this->ProductQuantity;
    }

    /**
     * @param int $ProductQuantity
     * @return AddBaseProduct
     */
    public function setProductQuantity($ProductQuantity)
    {
        $this->ProductQuantity = $ProductQuantity;
        return $this;
    }
}

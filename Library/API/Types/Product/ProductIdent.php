<?php

namespace Fatchip\Afterbuy\Types\Product;

class ProductIdent
{
    const TYPE_NO_CHILDREN = 0;
    const TYPE_VARIANT_SET = 1;
    const TYPE_PRODUCT_SET = 2;

    /** @var bool */
    private $ProductInsert;
    /** @var int */
    private $BaseProductType;
    /** @var string */
    private $UserProductID;
    /** @var int */
    private $ProductID;
    /** @var float */
    private $Anr;
    /** @var string */
    private $EAN;

    /**
     * ProductIdent constructor.
     * @param int $ProductID
     */
    public function __construct($ProductID = 0)
    {
        if (!empty($ProductID) && is_integer($ProductID)) {
            $this->setProductInsert(false)->setProductID($ProductID);
        } else {
            $this->setProductInsert(true);
        }
    }

    /**
     * @return bool
     */
    public function isProductInsert()
    {
        return $this->ProductInsert;
    }

    /**
     * @param bool $ProductInsert
     * @return ProductIdent
     */
    public function setProductInsert($ProductInsert)
    {
        $this->ProductInsert = $ProductInsert;
        return $this;
    }

    /**
     * @return int
     */
    public function getBaseProductType()
    {
        return $this->BaseProductType;
    }

    /**
     * @param int $BaseProductType
     * @return ProductIdent
     */
    public function setBaseProductType($BaseProductType)
    {
        $this->BaseProductType = $BaseProductType;
        return $this;
    }

    /**
     * @return string
     */
    public function getUserProductID()
    {
        return $this->UserProductID;
    }

    /**
     * @param string $UserProductID
     * @return ProductIdent
     */
    public function setUserProductID($UserProductID)
    {
        $this->UserProductID = $UserProductID;
        return $this;
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
     * @return ProductIdent
     */
    public function setProductID($ProductID)
    {
        $this->ProductID = $ProductID;
        return $this;
    }

    /**
     * @return float
     */
    public function getAnr()
    {
        return $this->Anr;
    }

    /**
     * @param float $Anr
     * @return ProductIdent
     */
    public function setAnr($Anr)
    {
        $this->Anr = $Anr;
        return $this;
    }

    /**
     * @return string
     */
    public function getEAN()
    {
        return $this->EAN;
    }

    /**
     * @param string $EAN
     * @return ProductIdent
     */
    public function setEAN($EAN)
    {
        $this->EAN = $EAN;
        return $this;
    }
}

<?php

namespace Fatchip\Afterbuy\Types\Product;

class AddBaseProducts
{
    const ACTION_UPDATE = 1;
    const ACTION_REPLACE = 2;

    /** @var int */
    private $UpdateAction;
    /** @var AddBaseProduct[] */
    private $AddBaseProduct;

    /**
     * AddBaseProducts constructor.
     */
    public function __construct()
    {
        $this->AddBaseProduct = [];
        $this->UpdateAction = self::ACTION_UPDATE;
    }

    /**
     * @return int
     */
    public function getUpdateAction()
    {
        return $this->UpdateAction;
    }

    /**
     * @param int $UpdateAction
     * @return AddBaseProducts
     */
    public function setUpdateAction($UpdateAction)
    {
        $this->UpdateAction = $UpdateAction;
        return $this;
    }

    /**
     * @return AddBaseProduct[]
     */
    public function getAddBaseProduct()
    {
        return $this->AddBaseProduct;
    }

    /**
     * @param AddBaseProduct[] $AddBaseProduct
     * @return AddBaseProducts
     */
    public function setAddBaseProduct($AddBaseProduct)
    {
        $this->AddBaseProduct = $AddBaseProduct;
        return $this;
    }
}

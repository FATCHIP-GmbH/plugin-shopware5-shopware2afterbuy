<?php

namespace Fatchip\Afterbuy\Types\Product;

class Skus
{
    /** @var int */
    private $UpdateAction;
    /** @var string[] */
    private $Sku;

    /**
     * @return int
     */
    public function getUpdateAction()
    {
        return $this->UpdateAction;
    }

    /**
     * @param int $UpdateAction
     * @return Skus
     */
    public function setUpdateAction($UpdateAction)
    {
        $this->UpdateAction = $UpdateAction;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getSku()
    {
        return $this->Sku;
    }

    /**
     * @param string[] $Sku
     * @return Skus
     */
    public function setSku($Sku)
    {
        $this->Sku = $Sku;
        return $this;
    }
}

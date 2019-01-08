<?php

namespace Fatchip\Afterbuy\Types\Product;

class AddAttributes
{
    /** @var int */
    private $UpdateAction;
    /** @var AddAttribut[] */
    private $AddAttribut;

    /**
     * @return int
     */
    public function getUpdateAction()
    {
        return $this->UpdateAction;
    }

    /**
     * @param int $UpdateAction
     * @return AddAttributes
     */
    public function setUpdateAction($UpdateAction)
    {
        $this->UpdateAction = $UpdateAction;
        return $this;
    }

    /**
     * @return AddAttribut[]
     */
    public function getAddAttribut()
    {
        return $this->AddAttribut;
    }

    /**
     * @param AddAttribut[] $AddAttribut
     * @return AddAttributes
     */
    public function setAddAttribut($AddAttribut)
    {
        $this->AddAttribut = $AddAttribut;
        return $this;
    }
}

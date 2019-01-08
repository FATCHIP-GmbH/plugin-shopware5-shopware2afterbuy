<?php

namespace Fatchip\Afterbuy\Types\Product;

class AddCatalogs
{
    /** @var int */
    private $UpdateAction;
    /** @var AddCatalog[] */
    private $AddCatalog;

    /**
     * @return int
     */
    public function getUpdateAction()
    {
        return $this->UpdateAction;
    }

    /**
     * @param int $UpdateAction
     * @return AddCatalogs
     */
    public function setUpdateAction($UpdateAction)
    {
        $this->UpdateAction = $UpdateAction;
        return $this;
    }

    /**
     * @return AddCatalog[]
     */
    public function getAddCatalog()
    {
        return $this->AddCatalog;
    }

    /**
     * @param AddCatalog[] $AddCatalog
     * @return AddCatalogs
     */
    public function setAddCatalog($AddCatalog)
    {
        $this->AddCatalog = $AddCatalog;
        return $this;
    }
}

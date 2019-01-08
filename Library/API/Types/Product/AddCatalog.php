<?php

namespace Fatchip\Afterbuy\Types\Product;

class AddCatalog
{
    /** @var int */
    private $CatalogID;
    /** @var string */
    private $CatalogName;
    /** @var int */
    private $CatalogLevel;

    /**
     * @return int
     */
    public function getCatalogID()
    {
        return $this->CatalogID;
    }

    /**
     * @param int $CatalogID
     * @return AddCatalog
     */
    public function setCatalogID($CatalogID)
    {
        $this->CatalogID = $CatalogID;
        return $this;
    }

    /**
     * @return string
     */
    public function getCatalogName()
    {
        return $this->CatalogName;
    }

    /**
     * @param string $CatalogName
     * @return AddCatalog
     */
    public function setCatalogName($CatalogName)
    {
        $this->CatalogName = $CatalogName;
        return $this;
    }

    /**
     * @return int
     */
    public function getCatalogLevel()
    {
        return $this->CatalogLevel;
    }

    /**
     * @param int $CatalogLevel
     * @return AddCatalog
     */
    public function setCatalogLevel($CatalogLevel)
    {
        $this->CatalogLevel = $CatalogLevel;
        return $this;
    }
}

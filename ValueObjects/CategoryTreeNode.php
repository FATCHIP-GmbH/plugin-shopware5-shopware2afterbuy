<?php

namespace viaebShopwareAfterbuy\ValueObjects;

use viaebShopwareAfterbuy\ValueObjects\Category as ValueCategory;

class CategoryTreeNode
{
    const NO_PARENT = null;
    const NO_CHILDREN = [];

    /** @var CategoryTreeNode $parent */
    private $parent;
    /** @var CategoryTreeNode[] $children */
    public $children;

    /** @var ValueCategory */
    private $valueCategory;

    public function __construct(ValueCategory $valueCategory)
    {
        $this->parent = self::NO_PARENT;
        $this->children = self::NO_CHILDREN;
        $this->valueCategory = $valueCategory;
    }

    /**
     * @return CategoryTreeNode
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param CategoryTreeNode $parent
     */
    public function setParent(CategoryTreeNode $parent)
    {
        $this->parent = $parent;
    }

    /**
     * @return ValueCategory
     */
    public function getValueCategory()
    {
        return $this->valueCategory;
    }
}

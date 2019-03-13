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
    private $children;

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
    public function getParent(): string
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
     * @return CategoryTreeNode[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @param CategoryTreeNode[] $children
     */
    public function setChildren(array $children)
    {
        $this->children = $children;
    }

    /**
     * @param CategoryTreeNode $child
     */
    public function addChild(CategoryTreeNode $child)
    {
        $this->children[] = $child;
    }

    /**
     * @return ValueCategory
     */
    public function getValueCategory(): ValueCategory
    {
        return $this->valueCategory;
    }
}

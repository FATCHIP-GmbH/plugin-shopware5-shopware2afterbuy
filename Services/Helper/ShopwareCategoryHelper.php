<?php

namespace FatchipAfterbuy\Services\Helper;

use Shopware\Models\Category\Category;

/**
 *
 * Class ShopwareCategoryHelper
 * @package FatchipAfterbuy\Services\Helper
 */

class ShopwareCategoryHelper extends AbstractHelper {

    /**
     * @return Category|null
     */
    public function getMainCategory() {
        return $this->entityManager->getRepository($this->entity)->findOneBy(array('id' => 1));
    }

    /**
     * moved here
     * @param \FatchipAfterbuy\ValueObjects\Category $category
     * @param string $identifier
     * @return Category
     */
    public function findParentCategory(\FatchipAfterbuy\ValueObjects\Category $category, string $identifier): Category
    {
        $parent = null;

        if ($category->getParentIdentifier()) {
            $parent = $this->getEntity(
                $category->getParentIdentifier(),
                $identifier,
                true,
                false
            );
        }

        if ( ! $parent) {
            $parent = $this->getMainCategory();
        }

        return $parent;
    }

}
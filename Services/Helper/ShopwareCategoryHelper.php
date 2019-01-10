<?php

namespace FatchipAfterbuy\Services\Helper;

use Shopware\Models\Category\Category;

/**
 * @Deprecated
 * Replaced by generic entity helper
 *
 * Class ShopwareCategoryHelper
 * @package FatchipAfterbuy\Services\Helper
 */

class ShopwareCategoryHelper extends AbstractHelper {

    /**
     * returns the category (Shopware\Models\Category\Category) by given identifier
     * if category does not exists a newer one will get created
     * @Deprecated
     *
     * @param string $identifier
     * @param string $field
     * @param bool $isAttribute
     * @return Category|null
     */
    public function getCategory(string $identifier, string $field, $isAttribute = false) {
        return $this->getEntity($identifier, $field, $isAttribute);
    }

    /**
     * @Deprecated
     *
     * @param string $identifier
     * @param string $field
     * @return Category|null
     */
    public function getCategoryByField(string $identifier, string $field) {
        return $this->getEntityByField($identifier, $field);
    }

    /**
     * @return Category|null
     */
    public function getMainCategory() {
        return $this->entityManager->getRepository($this->entity)->findOneBy(array('id' => 1));
    }

    /**
     * @Deprecated
     * get category by identifying attribute
     *
     * @param string $identifier
     * @param string $field
     * @return |null
     */
    public function getCategoryByAttribute(string $identifier, string $field) {
        return $this->getEntityByAttribute($identifier, $field);
    }

    /**
     * @Deprecated
     *
     * @param string $identifier
     * @param string $field
     * @param bool $isAttribute
     * @return Category
     */
    public function createCategory(string $identifier, string $field, $isAttribute = false) {
        return $this->createEntity($identifier, $field. $isAttribute);
    }


}
<?php

namespace FatchipAfterbuy\Services\Helper;

use FatchipAfterbuy\Components\Helper;
use Shopware\Components\Model\ModelEntity;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Category\Category;

class ShopwareCategoryHelper extends AbstractHelper {

    /**
     * @var ModelManager
     */
    protected $entityManager;

    /**
     * @var string
     */
    protected $entity;

    /**
     * @var string
     */
    protected $entityAttributes;

    /**
     * ShopwareCategoryHelper constructor.
     * @param ModelManager $entityManager
     * @param string $entity
     * @param string $entityAttributes
     */
    public function __construct(ModelManager $entityManager, string $entity, string $entityAttributes) {
        $this->entityManager = $entityManager;
        $this->entity = $entity;
        $this->entityAttributes = $entityAttributes;
    }

    /**
     * returns the category (Shopware\Models\Category\Category) by given identifier
     * if category does not exists a newer one will get created
     *
     * @param string $identifier
     * @param string $field
     * @param bool $isAttribute
     * @return Category|null
     */
    public function getCategory(string $identifier, string $field, $isAttribute = false) {
        if($isAttribute === true) {
            $category = $this->getCategoryByAttribute($identifier, $field);
        }
        else {
            $category = $this->getCategoryByField($identifier, $field);
        }

        if(!$category) {
            $category = $this->createCategory($identifier, $field, $isAttribute);
        }

        return $category;
    }

    /**
     * @param string $identifier
     * @param string $field
     * @return Category|null
     */
    public function getCategoryByField(string $identifier, string $field) {
        return $this->entityManager->getRepository($this->entity)->findOneBy(array($field => $identifier));
    }

    /**
     * @return Category|null
     */
    public function getMainCategory() {
        return $this->entityManager->getRepository($this->entity)->findOneBy(array('id' => 1));
    }

    /**
     * get category by identifying attribute
     *
     * @param string $identifier
     * @param string $field
     * @return |null
     */
    public function getCategoryByAttribute(string $identifier, string $field) {
        $attribute = $this->entityManager->getRepository($this->entityAttributes)->findOneBy(array($field => $identifier));

        if($attribute === null) {
            return null;
        }
        return $attribute->getCategory();
    }

    /**
     * @param string $identifier
     * @param string $field
     * @param bool $isAttribute
     * @return Category
     */
    public function createCategory(string $identifier, string $field, $isAttribute = false) {
        /**
         * @var Category $category
         */
        $category = new $this->entity();

        //we have to create attributes manually
        $attribute = new $this->entityAttributes();
        $category->setAttribute($attribute);

        $this->setIdentifier($identifier, $field, $category, $isAttribute);

        return $category;
    }

    /**
     * @param string $identifier
     * @param string $field
     * @param ModelEntity $category
     * @param $isAttribute
     */
    public function setIdentifier(string $identifier, string $field, ModelEntity $category, $isAttribute) {

        $setter = Helper::getSetterByField($field);

        if($isAttribute) {
            $category->getAttribute()->$setter($identifier);
        } else {
            $category->$setter($identifier);
        }
    }


}
<?php

namespace FatchipAfterbuy\Services\Helper;

use Shopware\Components\Model\ModelEntity;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Category\Category;

class ShopwareCategoryHelper extends AbstractHelper {

    protected $entityManager;

    protected $entity;

    protected $entityAttributes;

    public function __construct(ModelManager $entityManager, string $entity, string $entityAttributes) {
        $this->entityManager = $entityManager;
        $this->entity = $entity;
        $this->entityAttributes = $entityAttributes;
    }

    public function getCategory(string $identifier, string $field, $isAttribute = false) {
        if($isAttribute === true) {
            $category = $this->getCategoryByAttribute($identifier, $field);
        }
        else {
            $category = $this->entityManager->getRepository($this->entity)->findOneBy(array($field => $identifier));
        }

        if(!$category) {
            $category = $this->createCategory($identifier, $field, $isAttribute);
        }

        return $category;
    }

    public function getCategoryByAttribute(string $identifier, string $field) {
        $attribute = $this->entityManager->getRepository($this->entityAttributes)->findOneBy(array($field => $identifier));

        if($attribute === null) {
            return null;
        }
        return $attribute->getCategory();
    }

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

    public function setIdentifier(string $identifier, string $field, ModelEntity $category, $isAttribute) {

        $setter = 'set' . strtoupper($field[0]) . substr($field, 1);

        if($isAttribute) {
            $category->getAttribute()->$setter($identifier);
        } else {
            $category->$setter($identifier);
        }
    }


}
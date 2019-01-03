<?php

namespace FatchipAfterbuy\Services\Helper;

use Shopware\Components\Model\ModelManager;

class ShopwareCategoryHelper {

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
        return $this->entityManager->getRepository($this->entityAttributes)->findOneBy(array($field => $identifier));
    }

    public function createCategory(string $identifier, string $field, $isAttribute = false) {

    }

    public function setIdentifierAttribute(string $identifier, string $field) {

    }
}
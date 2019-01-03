<?php

namespace FatchipAfterbuy\Services\Helper;

use Shopware\Components\DependencyInjection\Bridge\Models;

class ShopwareCategoryHelper {

    protected $entityManager;

    public function __construct(Models $entityManager) {
        $this->entityManager = $entityManager;
    }

    public function getCategory(string $identifier, string $field, $isAttribute = false) {
        if($isAttribute === true) {

        }
        else {
            $this->entityManager->findOneBy();
        }

        if(!$category) {
            $category = $this->createCategory($identifier, $field, $isAttribute);
        }

        return $category;
    }

    public function getCategoryByAttribute(string $identifier, string $field) {

    }

    public function createCategory(string $identifier, string $field, $isAttribute = false) {

    }

    public function setIdentifierAttribute(string $identifier, string $field) {

    }
}
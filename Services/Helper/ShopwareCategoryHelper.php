<?php

namespace FatchipAfterbuy\Services\Helper;

use FatchipAfterbuy\ValueObjects\Category as ValueCategory;
use Shopware\Models\Category\Category as ShopwareCategory;

/**
 *
 * Class ShopwareCategoryHelper
 * @package FatchipAfterbuy\Services\Helper
 */

class ShopwareCategoryHelper extends AbstractHelper {

    /**
     * @return ShopwareCategory[]
     */
    public function getAllCategories(): array {
        return $this->entityManager->getRepository($this->entity)->findAll();
    }

    /**
     * @return ShopwareCategory|null
     */
    public function getMainCategory() {
        return $this->entityManager->getRepository($this->entity)->findOneBy(array('id' => 1));
    }

    /**
     * moved here
     *
     * @param ValueCategory $category
     * @param string        $identifier
     *
     * @return ShopwareCategory
     */
    public function findParentCategory(ValueCategory $category, string $identifier): ShopwareCategory
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

    /**
     * @param ValueCategory[] $valueCategories
     *
     * @return array
     */
    public function buildAfterbuyCatalogStructure(array $valueCategories): array
    {
        $valueCategories = $this->sortValueCategoriesByParentID($valueCategories);

        $catalogs = [];

        foreach ($valueCategories as $valueCategory) {
            $catalog = [
                'CatalogName' => $valueCategory->getName(),
                'CatalogDescription' => $valueCategory->getDescription(),
                'Position' => $valueCategory->getPosition(),
                'AdditionalText' => $valueCategory->getCmsText(),
                'ShowCatalog' => $valueCategory->getActive(),
                'Picture' => $valueCategory->getImage(),
                'InternalIdentifier' => $valueCategory->getInternalIdentifier(),
            ];

            $parentPath = array_reverse(explode('|', trim($valueCategory->getPath(), '|')));
            if ($parentPath === ['']) {
                $catalogs[] = $catalog;

                continue;
            }

            $currentParents = &$catalogs;
            foreach ($parentPath as $parentID) {
                foreach ($currentParents as &$currentParent) {
                    if ($currentParent['InternalIdentifier'] === $valueCategory->getParentIdentifier()) {
                        $currentParent['Catalog'][] = $catalog;

                        // next valueCategory
                        continue 3;
                    }

                    if ($currentParent['InternalIdentifier'] === $parentID) {
                        // if ( ! isset($currentParent['Catalog'])) {
                        //     $currentParent['Catalog'] = [];
                        // }
                        $currentParents = &$currentParent['Catalog'];

                        // next level
                        continue 2;
                    }
                }
            }
        }

        return $catalogs;
    }

    /**
     * @param ValueCategory[] $valueCategories
     *
     * @return ValueCategory[]
     */
    public function sortValueCategoriesByParentID(array $valueCategories): array
    {
        usort($valueCategories, [$this, 'compare']);

        return $valueCategories;
    }

    /**
     * @param ValueCategory $cat1
     * @param ValueCategory $cat2
     *
     * @return int
     */
    private function compare($cat1, $cat2): int
    {
        return ($cat1->getParentIdentifier() > $cat2->getParentIdentifier()) ? 1 : -1;
    }
}

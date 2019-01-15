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
     * @return Category[]
     */
    public function getAllCategories(): array {
        return $this->entityManager->getRepository($this->entity)->findAll();
    }

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

    /**
     * @param ValueCategory[] $valueCategories
     *
     * @return array
     */
    public function buildAfterbuyCatalogStructure(array $valueCategories): array
    {
        usort($valueCategories, [$this, 'compare']);

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

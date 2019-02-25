<?php

namespace abaccAfterbuy\Services\Helper;

use abaccAfterbuy\ValueObjects\Category as ValueCategory;
use Shopware\Models\Category\Category as ShopwareCategory;

/**
 *
 * Class ShopwareCategoryHelper
 * @package abaccAfterbuy\Services\Helper
 */

class ShopwareCategoryHelper extends AbstractHelper {

    /**
     * @return ShopwareCategory[]
     */
    public function getAllCategories() {
        return $this->entityManager->getRepository($this->entity)->findAll();
    }

    /**
     * @return ShopwareCategory|null
     */
    public function getMainCategory() :?ShopwareCategory {
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
    public function findParentCategory(ValueCategory $category, string $identifier)
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
    public function buildAfterbuyCatalogStructure(array $valueCategories)
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
                'CatalogID' => $valueCategory->getExternalIdentifier() ?: $valueCategory->getInternalIdentifier(),
            ];

            $parentPath = array_reverse(explode('|', trim($valueCategory->getPath(), '|')));
            if ($parentPath === ['']) {
                $catalogs[] = $catalog;

                continue;
            }

            $currentParents = &$catalogs;
            foreach ($parentPath as $parentID) {
                if (!is_array($currentParents)) {
                    continue;
                }

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
    public function sortValueCategoriesByParentID(array $valueCategories)
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
    private function compare($cat1, $cat2)
    {
        return ($cat1->getParentIdentifier() > $cat2->getParentIdentifier()) ? 1 : -1;
    }

    /**
     * @param array $ids
     * @throws \Zend_Db_Adapter_Exception
     */
    public function updateExternalIds(array $ids): void
    {
        $sql = '';

        //hotfix to avoid category duplicates
        foreach ($ids as $internalId=>$externalId) {
            $sql .= "UPDATE s_categories_attributes SET afterbuy_catalog_id = $externalId WHERE afterbuy_catalog_id = $internalId;";
        }

        foreach ($ids as $internalId=>$externalId) {
            $sql .= "INSERT INTO s_categories_attributes (categoryID, afterbuy_catalog_id)
VALUES ($internalId, $externalId)
ON duplicate key update afterbuy_catalog_id = $externalId;";
        }

        if(!empty($sql)) {
            $this->db->query($sql);
        }
    }

    /**
     * @param array $response
     * @return array
     */
    public function getCatalogIdsFromResponse(array $response): array
    {
        $catalogIds = [];

        if(!is_array($response)) {
            return $catalogIds;
        }

        if(!array_key_exists('Result', $response) || !array_key_exists('NewCatalogs', $response['Result'])) {
            return $catalogIds;
        }

        foreach($response['Result']['NewCatalogs'] as $newCatalog) {
            if(array_key_exists(1, $newCatalog)) {
                foreach ($newCatalog as $sub) {

                    if(array_key_exists('CatalogID', $sub) && array_key_exists('CatalogIDRequested', $sub)) {
                        $catalogIds[$sub['CatalogIDRequested']] = $sub['CatalogID'];
                    }

                    $catalogIds = $this->getCatalogIdsRecursiveFromResponse($sub, $catalogIds);
                }
            }
            else {
                $catalogIds[$newCatalog['CatalogIDRequested']] = $newCatalog['CatalogID'];

                $catalogIds = $this->getCatalogIdsRecursiveFromResponse($newCatalog, $catalogIds);
            }
        }

        return $catalogIds;
    }

    /**
     * @param $array
     * @param array $ids
     * @return array
     */
    public function getCatalogIdsRecursiveFromResponse($array, &$ids = []): array
    {

        if(is_array($array) && array_key_exists('NewCatalog', $array)) {

            foreach ($array['NewCatalog'] as $newCatalog) {

                if(is_array($newCatalog) && array_key_exists('CatalogID', $newCatalog)) {
                    $ids[$newCatalog['CatalogIDRequested']] = $newCatalog['CatalogID'];
                }

                $ids = $this->getCatalogIdsRecursiveFromResponse($newCatalog, $ids);
            }
        }

        return $ids;
    }
}

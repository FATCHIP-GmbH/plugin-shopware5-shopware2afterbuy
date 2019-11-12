<?php
/** @noinspection SpellCheckingInspection */

namespace viaebShopwareAfterbuy\Services\Helper;

use Exception;
use Shopware\Components\Model\QueryBuilder;
use viaebShopwareAfterbuy\ValueObjects\CategoryTreeNode;
use viaebShopwareAfterbuy\ValueObjects\Category as ValueCategory;
use Shopware\Models\Category\Category as ShopwareCategory;
use Zend_Db_Adapter_Exception;

/**
 *
 * Class ShopwareCategoryHelper
 * @package viaebShopwareAfterbuy\Services\Helper
 */

class ShopwareCategoryHelper extends AbstractHelper
{
    /**
     * Returns all categories including 'Root' category.
     *
     * @noinspection PhpUnused
     * @return ShopwareCategory[]
     */
    public function getAllCategories()
    {
        return $this->entityManager->getRepository($this->entity)->findAll();
    }

    /**
     * @param array $filter
     * @return ShopwareCategory[]
     * @see QueryBuilder::addFilter()
     */
    public function getFilteredCategoryList(array $filter = []) {
        $defaultFilter = [
            'property' => 'parent',
            'expression' => '!=',
            'value' => 'NULL',
        ];

        $filter[] = $defaultFilter;

        /** @var QueryBuilder $builder */
        $builder = $this->entityManager->getRepository($this->entity)->createQueryBuilder('category');
        $builder->select([]);
        $builder->addFilter($filter);

        return $builder->getQuery()->getResult();
    }

    /**
     * @return ShopwareCategory|object|null
     */
    public function getMainCategory()
    {
        $baseCategoryId = 1;

        if (array_key_exists('baseCategory', $this->config) && $this->config['baseCategory']) {
            $baseCategoryId = $this->config['baseCategory'];
        }

        return $this->entityManager->getRepository($this->entity)->findOneBy(array('id' => $baseCategoryId));
    }

    /**
     * moved here
     *
     * @param ValueCategory $category
     * @param string $identifier
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

        if (!$parent) {
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
                    if ($currentParent['InternalIdentifier'] == $valueCategory->getParentIdentifier()) {
                        $currentParent['Catalog'][] = $catalog;

                        // next valueCategory
                        continue 3;
                    }

                    if ($currentParent['InternalIdentifier'] == $parentID) {
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
     * @param CategoryTreeNode[] $valueCategories
     *
     * @return CategoryTreeNode[]
     */
    public function createCategoryTrees(array $valueCategories)
    {
        /** @var CategoryTreeNode[] $list */
        $list = [];
        /** @var CategoryTreeNode[] $tree */
        $trees = [];

        /** @var ValueCategory $category */
        foreach ($valueCategories as $category) {
            $list[$category->getExternalIdentifier()] = new CategoryTreeNode($category);
        }

        /**
         * @var int $externalId
         * @var CategoryTreeNode $node
         */
        foreach ($list as $externalId => $node) {
            $parentId = $node->getValueCategory()->getParentIdentifier();

            if ($parentId === '0') {
                $trees[] = $node;
            } else {
                $parentNode = $list[$parentId];

                $node->setParent($parentNode);
                $parentNode->children[] = $node;
            }
        }

        return $trees;
    }

    /**
     * @param CategoryTreeNode[] $valueCategoryTrees
     * @return ShopwareCategory[]
     */
    public function addCategoriesToShopware(array $valueCategoryTrees)
    {
        /** @var ShopwareCategory[] $shopwareCategories */
        $shopwareCategories = [];

        /** @var CategoryTreeNode $current */
        foreach ($valueCategoryTrees as $current) {
            $stack = [];

            do {
                // walk to leftest children
                do {
                    $externalIdentifier = $current->getValueCategory()->getExternalIdentifier();
                    if (!in_array($externalIdentifier, $shopwareCategories)) {
                        $shopwareCategories[$externalIdentifier] = $this->createShopwareCategory(
                            $current->getValueCategory()
                        );

                        $shopwareCategories[$externalIdentifier]->setParent(
                            $this->findParentCategory($current->getValueCategory(), 'afterbuyCatalogId')
                        );

                        try {
                            $this->entityManager->persist($shopwareCategories[$externalIdentifier]);
                            $this->entityManager->flush();
                        } catch (Exception $e) {
                            $this->logger->error(
                                'Error saving category',
                                array(json_encode($current->getValueCategory()))
                            );
                        }
                    }

                    if ($current->children !== CategoryTreeNode::NO_CHILDREN) {
                        $stack[] = $current;

                        $current = array_pop($current->children);
                    } else {
                        break;
                    }
                } while (true);

                $current = array_pop($stack);
            } while ($current !== null);
        }

        return $shopwareCategories;
    }

    /**
     * @param ValueCategory $valueCategory
     *
     * @return ShopwareCategory
     */
    private function createShopwareCategory(ValueCategory $valueCategory)
    {
        /**
         * @var ShopwareCategory $shopwareCategory
         */
        $shopwareCategory = $this->getEntity($valueCategory->getExternalIdentifier(), 'afterbuyCatalogId', true);

        $shopwareCategory->setName($valueCategory->getName());
        $shopwareCategory->setMetaDescription($valueCategory->getDescription());

        $shopwareCategory->setPosition($valueCategory->getPosition());
        $shopwareCategory->setCmsText($valueCategory->getCmsText());
        $shopwareCategory->setActive($valueCategory->getActive());

        return $shopwareCategory;
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
     * @throws Zend_Db_Adapter_Exception
     */
    public function updateExternalIds(array $ids)
    {
        $sql = '';

        //hotfix to avoid category duplicates
        foreach ($ids as $internalId => $externalId) {
            $sql .= "UPDATE s_categories_attributes SET afterbuy_catalog_id = $externalId WHERE afterbuy_catalog_id = $internalId;";
        }

        foreach ($ids as $internalId => $externalId) {
            $sql .= "INSERT INTO s_categories_attributes (categoryID, afterbuy_catalog_id)
VALUES ($internalId, $externalId)
ON duplicate key update afterbuy_catalog_id = $externalId;";
        }

        if (!empty($sql)) {
            $this->db->query($sql);
        }
    }

    /**
     * @param array $response
     * @return array
     */
    public function getCatalogIdsFromResponse(array $response)
    {
        $catalogIds = [];

        if (!is_array($response)) {
            return $catalogIds;
        }

        if (
            !array_key_exists('Result', $response)
            || !array_key_exists('NewCatalogs', $response['Result'])
            || !array_key_exists('NewCatalog', $response['Result']['NewCatalogs'])
        ) {
            return $catalogIds;
        }

        $catalogStack = [$response['Result']['NewCatalogs']['NewCatalog']];
        while ($catalogStack !== []) {
            $newCatalog = array_pop($catalogStack);

            if (is_array($newCatalog) && !array_key_exists(0, $newCatalog)) {
                // single subcategory
                $newCatalog = [$newCatalog];
            }

            foreach ($newCatalog as $catalog) {
                $catalogIds[$catalog['CatalogIDRequested']] = $catalog['CatalogID'];

                if (array_key_exists('NewCatalog', $catalog)) {
                    $catalogStack[] = $catalog['NewCatalog'];
                }
            }
        }

        return $catalogIds;
    }
}

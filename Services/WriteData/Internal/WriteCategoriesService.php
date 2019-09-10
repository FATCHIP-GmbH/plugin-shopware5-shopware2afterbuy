<?php

namespace viaebShopwareAfterbuy\Services\WriteData\Internal;

use Doctrine\ORM\ORMException;
use viaebShopwareAfterbuy\Services\Helper\ShopwareCategoryHelper;
use viaebShopwareAfterbuy\Services\WriteData\AbstractWriteDataService;
use viaebShopwareAfterbuy\Services\WriteData\WriteDataInterface;
use viaebShopwareAfterbuy\ValueObjects\Category as ValueCategory;
use viaebShopwareAfterbuy\ValueObjects\CategoryTreeNode;

class WriteCategoriesService extends AbstractWriteDataService implements WriteDataInterface
{
    /**
     * @var string $identifier
     */
    protected $identifier;

    /**
     * @var bool $isAttribute
     */
    protected $isAttribute;

    /**
     * @param array $data
     * @return mixed
     * @throws ORMException
     */
    public function put(array $data)
    {
        return $this->transform($data);
    }

    /**
     * transforms valueObject into final structure for storage
     * could may be moved into separate helper
     *
     * @param ValueCategory[] $valueCategories
     *
     * @return mixed
     * @throws ORMException
     */
    public function transform(array $valueCategories)
    {
        /**
         * @var ShopwareCategoryHelper $categoryHelper
         */
        $categoryHelper = $this->helper;

        $this->logger->info('Storing ' . count($valueCategories) . ' items.', array('Categories', 'Write', 'Internal'));

        /** @var CategoryTreeNode[] $valueCategoryTrees */
        $valueCategoryTrees = $categoryHelper->createCategoryTrees($valueCategories);

        $shopwareCategories = $categoryHelper->addCategoriesToShopware($valueCategoryTrees);

//        $valueCategories = $categoryHelper->sortValueCategoriesByParentID($valueCategories);
//
//        foreach ($valueCategories as $valueCategory) {
//            /**
//             * @var ShopwareCategory $shopwareCategory
//             */
//            $shopwareCategory = $categoryHelper->getEntity(
//                $valueCategory->getExternalIdentifier(),
//                $this->identifier,
//                $this->isAttribute
//            );
//
//            $shopwareCategory->setName($valueCategory->getName());
//            $shopwareCategory->setMetaDescription($valueCategory->getDescription());
//
//            if($shopwareCategory->getParent() === null) {
//                $shopwareCategory->setParent($categoryHelper->findParentCategory($valueCategory, $this->identifier));
//            }
//
//            $shopwareCategory->setPosition($valueCategory->getPosition());
//            $shopwareCategory->setCmsText($valueCategory->getCmsText());
//            $shopwareCategory->setActive($valueCategory->getActive());
//
//            $this->entityManager->persist($shopwareCategory);
//
//            try {
//                $this->entityManager->flush($shopwareCategory);
//            } catch (OptimisticLockException $e) {
//                $this->logger->error('Error saving category', array(json_encode($valueCategory)));
//            }
//        }

        return $shopwareCategories;
    }

    /**
     * @param $targetData
     *
     * @return mixed
     */
    public function send($targetData)
    {
        return $targetData;
    }
}

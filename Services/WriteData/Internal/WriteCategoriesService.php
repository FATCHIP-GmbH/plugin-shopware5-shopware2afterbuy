<?php

namespace FatchipAfterbuy\Services\WriteData\Internal;

use Doctrine\ORM\OptimisticLockException;
use FatchipAfterbuy\Services\Helper\AbstractHelper;
use FatchipAfterbuy\Services\Helper\ShopwareCategoryHelper;
use FatchipAfterbuy\Services\WriteData\AbstractWriteDataService;
use FatchipAfterbuy\Services\WriteData\WriteDataInterface;
use FatchipAfterbuy\ValueObjects\Category as ValueCategory;
use Shopware\Models\Category\Category as ShopwareCategory;

class WriteCategoriesService extends AbstractWriteDataService implements WriteDataInterface
{

    /**
     * @var ShopwareCategoryHelper $categoryHelper
     */
    protected $categoryHelper;

    /**
     * @var string $identifier
     */
    protected $identifier;

    /**
     * @var bool $isAttribute
     */
    protected $isAttribute;

    /**
     * @param AbstractHelper $helper
     * @param string         $identifier
     * @param bool           $isAttribute
     */
    public function initHelper(AbstractHelper $helper, string $identifier, bool $isAttribute)
    {
        $this->categoryHelper = $helper;
        $this->identifier = $identifier;
        $this->isAttribute = $isAttribute;
    }

    /**
     * @param array $data
     *
     * @return mixed|void
     * @throws OptimisticLockException
     */
    public function put(array $data)
    {
        $data = $this->transform($data);

        return $this->send($data);
    }

    /**
     * transforms valueObject into final structure for storage
     * could may be moved into separate helper
     *
     * @param ValueCategory[] $data
     *
     * @return mixed|void
     */
    public function transform(array $data)
    {
        $this->logger->info('Storing ' . count($data) . ' items.', array('Categories', 'Write', 'Internal'));

        foreach ($data as $category) {
            /**
             * @var ShopwareCategory $shopwareCategory
             */
            $shopwareCategory = $this->categoryHelper->getCategory(
                $category->getExternalIdentifier(),
                $this->identifier,
                $this->isAttribute
            );

            $shopwareCategory->setName($category->getName());
            $shopwareCategory->setMetaDescription($category->getDescription());
            $shopwareCategory->setParent($this->findParent($category));
            $shopwareCategory->setPosition($category->getPosition());
            $shopwareCategory->setCmsText($category->getCmsText());
            $shopwareCategory->setActive($category->getActive());

            $this->entityManager->persist($shopwareCategory);
        }
    }


    /**
     * @param $targetData
     *
     * @return mixed|void
     * @throws OptimisticLockException
     */
    public function send($targetData)
    {
        $this->entityManager->flush();
    }

    /**
     * @param ValueCategory $category
     *
     * @return ShopwareCategory
     */
    private function findParent(ValueCategory $category): ShopwareCategory
    {
        $parent = null;

        if ($category->getParentIdentifier()) {
            $parent = $this->categoryHelper->getCategoryByAttribute(
                $category->getParentIdentifier(),
                $this->identifier
            );
        }

        if ( ! $parent) {
            $parent = $this->categoryHelper->getMainCategory();
        }

        return $parent;
    }
}
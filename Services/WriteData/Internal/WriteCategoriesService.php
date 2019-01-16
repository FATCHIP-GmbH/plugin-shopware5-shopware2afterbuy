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
     * @param ValueCategory[] $valueCategories
     *
     * @return mixed|void
     */
    public function transform(array $valueCategories)
    {
        $this->logger->info('Storing ' . count($valueCategories) . ' items.', array('Categories', 'Write', 'Internal'));

        foreach ($valueCategories as $valueCategory) {
            /**
             * @var ShopwareCategory $shopwareCategory
             */
            $shopwareCategory = $this->categoryHelper->getEntity(
                $valueCategory->getExternalIdentifier(),
                $this->identifier,
                $this->isAttribute
            );

            $shopwareCategory->setName($valueCategory->getName());
            $shopwareCategory->setMetaDescription($valueCategory->getDescription());
            $shopwareCategory->setParent($this->categoryHelper->findParentCategory($valueCategory, $this->identifier));
            $shopwareCategory->setPosition($valueCategory->getPosition());
            $shopwareCategory->setCmsText($valueCategory->getCmsText());
            $shopwareCategory->setActive($valueCategory->getActive());

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
}

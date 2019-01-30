<?php

namespace FatchipAfterbuy\Services\WriteData\Internal;

use Doctrine\ORM\OptimisticLockException;
use FatchipAfterbuy\Services\Helper\ShopwareCategoryHelper;
use FatchipAfterbuy\Services\WriteData\AbstractWriteDataService;
use FatchipAfterbuy\Services\WriteData\WriteDataInterface;
use FatchipAfterbuy\ValueObjects\Category as ValueCategory;
use Shopware\Models\Category\Category as ShopwareCategory;

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
     */
    public function put(array $data)
    {
        $this->transform($data);
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
        /**
         * @var ShopwareCategoryHelper $categoryHelper
         */
        $categoryHelper = $this->helper;

        $this->logger->info('Storing ' . count($valueCategories) . ' items.', array('Categories', 'Write', 'Internal'));

        $valueCategories = $categoryHelper->sortValueCategoriesByParentID($valueCategories);

        foreach ($valueCategories as $valueCategory) {
            /**
             * @var ShopwareCategory $shopwareCategory
             */
            $shopwareCategory = $categoryHelper->getEntity(
                $valueCategory->getExternalIdentifier(),
                $this->identifier,
                $this->isAttribute
            );

            $shopwareCategory->setName($valueCategory->getName());
            $shopwareCategory->setMetaDescription($valueCategory->getDescription());
            $shopwareCategory->setParent($categoryHelper->findParentCategory($valueCategory, $this->identifier));
            $shopwareCategory->setPosition($valueCategory->getPosition());
            $shopwareCategory->setCmsText($valueCategory->getCmsText());
            $shopwareCategory->setActive($valueCategory->getActive());

            // TODO: create config for album name
            $media = $this->helper->createMediaImage(
                $valueCategory->getImage(),
                'Categories'
            );

            $shopwareCategory->setMedia($media);

            $this->entityManager->persist($shopwareCategory);

            try {
                $this->entityManager->flush($shopwareCategory);
            } catch (OptimisticLockException $e) {
                // TODO: log error
            }
        }
    }

    /**
     * @param $targetData
     *
     * @return mixed|void
     */
    public function send($targetData)
    {
    }
}

<?php

namespace FatchipAfterbuy\Services\WriteData\External;

use FatchipAfterbuy\Services\Helper\AbstractHelper;
use FatchipAfterbuy\Services\Helper\ShopwareCategoryHelper;
use FatchipAfterbuy\Services\WriteData\AbstractWriteDataService;
use FatchipAfterbuy\Services\WriteData\WriteDataInterface;
use FatchipAfterbuy\ValueObjects\Category;

class WriteOrdersService extends AbstractWriteDataService implements WriteDataInterface {

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
     * @param string $identifier
     * @param bool $isAttribute
     */
    public function initHelper(string $identifier, bool $isAttribute) {
        $this->identifier = $identifier;
        $this->isAttribute = $isAttribute;
    }

    /**
     * @param array $data
     * @return mixed|void
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function put(array $data) {
        $data = $this->transform($data);
        return $this->send($data);
    }

    /**
     * transforms valueObject into final structure for storage
     * could may be moved into separate helper
     *
     * @param array $data
     * @return mixed|void
     */
    public function transform(array $data) {

        $this->logger->info("Storing " . count($data) . " items.", array("Categories", "Write", "Internal"));

        foreach($data as $value) {
            /**
             * @var Category $value
             */

            // define variable
            $parent = null;

            /**
             * @var \Shopware\Models\Category\Category $category
             */
            $category = $this->categoryHelper->getCategory($value->getExternalIdentifier(), $this->identifier, $this->isAttribute);

            /**
             * set category values
             */
            $category->setName($value->getName());
            $category->setMetaDescription($value->getDescription());

            if($value->getParentIdentifier()) {
                $parent = $this->categoryHelper->getCategoryByAttribute($value->getParentIdentifier(), $this->identifier);
            }

            if(!$parent) {
                $parent = $this->categoryHelper->getMainCategory();
            }

            $category->setParent($parent);

            $this->entityManager->persist($category);
        }
    }


    /**
     * @param $targetData
     * @return mixed|void
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function send($targetData) {
        $this->entityManager->flush();
    }
}
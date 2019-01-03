<?php

namespace FatchipAfterbuy\Services\WriteData\Internal;

use FatchipAfterbuy\Services\Helper\AbstractHelper;
use FatchipAfterbuy\Services\Helper\ShopwareCategoryHelper;
use FatchipAfterbuy\Services\WriteData\AbstractWriteDataService;
use FatchipAfterbuy\Services\WriteData\WriteDataInterface;
use FatchipAfterbuy\ValueObjects\Category;


class WriteCategoriesService extends AbstractWriteDataService implements WriteDataInterface {

    /**
     * @var ShopwareCategoryHelper $categoryHelper
     */
    protected $categoryHelper;

    public function initHelper(AbstractHelper $helper) {
        $this->categoryHelper = $helper;
    }

    public function put(array $data) {
        $data = $this->transform($data);
        return $this->send($data);
    }

    //TODO: transform handler?
    public function transform(array $data) {
        //TODO: into component

        foreach($data as $value) {
            /**
             * @var Category $value
             */
            //TODO: get category
            //TODO: set category values
            //TODO: inject category field
            /**
             * @var \Shopware\Models\Category\Category $category
             */
            $category = $this->categoryHelper->getCategory($value->getExternalIdentifier(), 'afterbuyCatalogId', true);

            /**
             * set category values
             */
            $category->setName($value->getName());
            $category->setMetaDescription($value->getDescription());

            //TODO: add correct parent
            $category->setParent($this->categoryHelper->getCategory(3, 'id'));

            $this->entityManager->persist($category);
        }
    }

    /**
     *
     * Type varies depending on target plattform
     * @param $targetData
     */
    public function send($targetData) {
        $this->entityManager->flush();
    }
}
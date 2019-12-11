<?php

namespace viaebShopwareAfterbuy\Services\WriteData\Internal;

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

<?php

namespace FatchipAfterbuy\Services\ReadData\Internal;

use FatchipAfterbuy\Services\Helper\AbstractHelper;
use FatchipAfterbuy\Services\Helper\ShopwareCategoryHelper;
use FatchipAfterbuy\Services\ReadData\AbstractReadDataService;
use FatchipAfterbuy\Services\ReadData\ReadDataInterface;
use FatchipAfterbuy\ValueObjects\Category as ValueCategory;
use Shopware\Bundle\MediaBundle\MediaService;
use Shopware\Models\Category\Category as ShopwareCategory;

class ReadCategoriesService extends AbstractReadDataService implements ReadDataInterface
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
     * @param array $filter
     *
     * @return ValueCategory[]
     */
    public function get(array $filter): array
    {
        $data = $this->read($filter);

        return $this->transform($data);
    }

    /**
     * transforms api input into valueObject (targetEntity)
     *
     * @param ShopwareCategory[] $shopwareCategories
     *
     * @return ValueCategory[]
     */
    public function transform(array $shopwareCategories): array
    {
        if ($this->targetEntity === null) {

            $this->logger->error('No target entity defined!', ['Categories', 'Read', 'Internal']);

            return null;
        }

        $this->logger->info('Got ' . count($shopwareCategories) . ' items', ['Categories', 'Read', 'Internal']);

        $valueCategories = [];

        foreach ($shopwareCategories as $shopwareCategory) {
            /** @var ValueCategory $valueCategory */
            $valueCategory = new $this->targetEntity();

            $valueCategory->setParentIdentifier($shopwareCategory->getParentId());
            $valueCategory->setName($shopwareCategory->getName());
            $valueCategory->setPosition($shopwareCategory->getPosition());
            $valueCategory->setDescription($shopwareCategory->getMetaDescription());
            $valueCategory->setCmsText($shopwareCategory->getCmsText());
            $valueCategory->setActive($shopwareCategory->getActive());
            $valueCategory->setInternalIdentifier($shopwareCategory->getId());
            $valueCategory->setPath($shopwareCategory->getPath());
            if (($media = $shopwareCategory->getMedia()) && $media->getId() > 0) {
                /** @var MediaService $mediaService */
                $mediaService = Shopware()->Container()->get('shopware_media.media_service');
                $image = $mediaService->getUrl($media->getPath());

                $valueCategory->setImage($image);
            }
            // TODO: handle media

            if ($valueCategory->isValid()) {
                $valueCategories[] = $valueCategory;
            } else {
                // TODO: log error message
            }
        }

        return $valueCategories;
    }


    /**
     * provides api data. dummy data as used here can be used in tests
     *
     * @param array $filter
     *
     * @return ShopwareCategory[]
     */
    public function read(array $filter): array
    {
        /**
         * @var ShopwareCategoryHelper $categoryHelper
         */
        $categoryHelper = $this->helper;

        return $categoryHelper->getAllCategories();
    }
}

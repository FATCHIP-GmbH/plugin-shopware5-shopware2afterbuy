<?php

namespace FatchipAfterbuy\Services\WriteData\Internal;

use DateTime;
use Doctrine\ORM\OptimisticLockException;
use Exception;
use FatchipAfterbuy\Services\Helper\ShopwareCategoryHelper;
use FatchipAfterbuy\Services\WriteData\AbstractWriteDataService;
use FatchipAfterbuy\Services\WriteData\WriteDataInterface;
use FatchipAfterbuy\ValueObjects\Category as ValueCategory;
use Shopware\Bundle\MediaBundle\MediaService;
use Shopware\Models\Category\Category as ShopwareCategory;
use Shopware\Models\Media\Media;

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

            $media = $this->createMedia($valueCategory->getName(), $valueCategory->getImage());

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
     * @param $name
     * @param $url
     *
     * @return Media
     */
    private function createMedia($name, $url): Media
    {
        $path = 'media/image/' . $name . '.jpg';
        $contents = file_get_contents($url);

        /** @var MediaService $mediaService */
        $mediaService = Shopware()->Container()->get('shopware_media.media_service');
        $mediaService->write($path, $contents);

        $media = new Media();
        $media->setAlbumId(-1);
        $media->setDescription('');
        try {
            $media->setCreated(new DateTime());
        } catch (Exception $e) {
            // TODO: handle exception
        }
        $media->setUserId(0);
        $media->setName($name);
        $media->setPath($path);
        $media->setFileSize($mediaService->getSize($path));
        $media->setExtension('jpg');
        $media->setType('image');

        $this->entityManager->persist($media);
        try {
            $this->entityManager->flush($media);
        } catch (OptimisticLockException $e) {
        }

        return $media;
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

<?php

namespace FatchipAfterbuy\Services\ReadData\Internal;

use Fatchip\Afterbuy\ApiClient;
use FatchipAfterbuy\Components\Helper;
use FatchipAfterbuy\Services\ReadData\AbstractReadDataService;
use FatchipAfterbuy\Services\ReadData\ReadDataInterface;
use FatchipAfterbuy\ValueObjects\Address;
use FatchipAfterbuy\ValueObjects\Article;
use FatchipAfterbuy\ValueObjects\Order;
use FatchipAfterbuy\ValueObjects\OrderPosition;
use FatchipAfterbuy\ValueObjects\ProductPicture;
use Shopware\Models\Article\Configurator\Option;
use Shopware\Models\Article\Price;
use Shopware\Models\Customer\Group;

class ReadProductsService extends AbstractReadDataService implements ReadDataInterface {

    protected $customerGroup;

    /**
     * @param array $filter
     * @return array|null
     */
    public function get(array $filter) {
        $data = $this->read($filter);
        return $this->transform($data);
    }

    /**
     * transforms api input into valueObject (targetEntity)
     *
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function transform(array $data) {
        //TODO: refactor

        if($this->targetEntity === null) {
            return array();
        }

        $this->customerGroup = $this->entityManager->getRepository(Group::class)->findOneBy(
            array('id' => $this->config['customerGroup'])
        );

        $netInput = $this->customerGroup->getTaxInput();

        $targetData = array();

        foreach($data as $entity) {

            /**
             * @var \Shopware\Models\Article\Article $entity
             */

            if(empty($entity) || is_null($entity->getTax())) {
                continue;
            }

            /**
             * @var Article $article
             */
            $article = new $this->targetEntity();

            $article->setActive($entity->getActive());
            $article->setName($entity->getName());
            $article->setMainArticleId($entity->getId());


            $article->setDescription($entity->getDescriptionLong());
            $article->setShortDescription($entity->getDescription());

            $article->setTax($entity->getTax()->getTax());

            $article->setManufacturer($entity->getSupplier()->getName());

            /**
             * article images
             */

            $images = $entity->getImages();

            if($images->count()) {
                foreach($images as $image) {
                    $path = $image->getMedia()->getPath();
                    $url = $this->mediaService->getUrl($path);

                    if($image->getMain() == 1) {
                        $article->setMainImageUrl($url);

                        $thumbnails = $image->getMedia()->getThumbnails();

                        if(is_array($thumbnails)) {
                            $thumbnail = reset($thumbnails);
                            $thumbnailUrl = $this->mediaService->getUrl($thumbnail);
                        }

                        $article->setMainImageThumbnailUrl($thumbnailUrl);
                    }

                    if(is_null($image->getChildren()) || $image->getChildren()->count() === 0) {

                        if($image->getMain() == 1) {
                            continue;
                        }

                        $productPicture = new ProductPicture();
                        $productPicture->setAltText($entity->getName() . '_' . ((int)$image->getPosition()));
                        $productPicture->setNr($image->getPosition());
                        $productPicture->setUrl($url);

                        $article->addProductPicture($productPicture);
                    }
                }
            }

            if(!$entity->getConfiguratorSet()) {
                //simple article

                $detail = $entity->getMainDetail();

                if($detail->getEan()) {
                    $article->setEan($detail->getEan());
                }
                $article->setInternalIdentifier($detail->getNumber());
                $article->setStockMin($detail->getStockMin());
                $article->setStock($detail->getInStock());

                $price = $detail->getPrices()->filter(function(Price $price) {
                    return $price->getCustomerGroup() === $this->customerGroup;
                })->first();


                $price = Helper::convertPrice($price->getPrice(), $entity->getTax()->getTax(), $netInput, false);

                $article->setPrice($price);

                $article->setExternalIdentifier($detail->getAttribute()->getAfterbuyId());
                $article->setSupplierNumber($detail->getSupplierNumber());

                $article->setVariantId($detail->getId());

                $article->setVariantArticles(null);
            }
            else {
                $article->setInternalIdentifier('AB' . $entity->getMainDetail()->getNumber());

                foreach ($entity->getDetails() as $detail) {

                    /**
                     * @var \Shopware\Models\Article\Detail $detail
                     */

                    /**
                     * @var Article $variant
                     */
                    $variant = new $this->targetEntity();

                    if($detail->getEan()) {
                        $variant->setEan($detail->getEan());
                    }

                    $variant->setInternalIdentifier($detail->getNumber());
                    $variant->setStockMin($detail->getStockMin());
                    $variant->setStock($detail->getInStock());
                    $variant->setSupplierNumber($detail->getSupplierNumber());
                    $variant->setVariantId($detail->getId());
                    $variant->setExternalIdentifier($detail->getAttribute()->getAfterbuyId());

                    $price = $detail->getPrices()->filter(function(Price $price) {
                        return $price->getCustomerGroup() === $this->customerGroup;
                    })->first();

                    $options = [];

                    foreach($detail->getConfiguratorOptions() as $option) {
                        /**
                         * @var Option $option
                         */

                        $options[$option->getGroup()->getName()] = $option->getName();
                    }
                    // we have to take care that the order of variant options stays the same
                    ksort($options);

                    $variant->setVariants($options);

                    $variant->setName($entity->getName() . ' ' . implode(" ", array_values($options)));

                    $price = Helper::convertPrice($price->getPrice(), $entity->getTax()->getTax(), $netInput, false);

                    $variant->setPrice($price);

                    $variant->setExternalIdentifier($detail->getAttribute()->getAfterbuyId());

                    /**
                     * variant images
                     */

                    $images = $detail->getImages();

                    if($images->count()) {
                        foreach($images as $index=>$image) {
                            $path = $image->getParent()->getMedia()->getPath();
                            $url = $this->mediaService->getUrl($path);

                            if($index === 0) {
                                $thumbnails = $image->getParent()->getMedia()->getThumbnails();

                                if(is_array($thumbnails)) {
                                    $thumbnail = reset($thumbnails);
                                    $thumbnailUrl = $this->mediaService->getUrl($thumbnail);
                                }

                                $variant->setMainImageUrl($url);
                                $variant->setMainImageThumbnailUrl($thumbnailUrl);
                                continue;
                            }

                            $productPicture = new ProductPicture();
                            $productPicture->setAltText($variant->getName() . '_' . ( (int) $image->getPosition()));
                            $productPicture->setNr($image->getPosition() +1);
                            $productPicture->setUrl($url);

                            $variant->addProductPicture($productPicture);
                        }
                    }

                    $article->getVariantArticles()->add($variant);
                }
            }

            $targetData[] = $article;

        }

        return $targetData;
    }


    /**
     * provides api data. dummy data as used here can be used in tests
     *
     * @param array $filter
     * @return array
     */
    public function read(array $filter) {

        $data = $this->helper->getUnexportedArticles($filter['submitAll']);

        if(!$data || empty($data)) {
            return array();
        }

        return $data;
    }
}
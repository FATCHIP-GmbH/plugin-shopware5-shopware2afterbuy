<?php

namespace FatchipAfterbuy\Services\WriteData\Internal;

use Doctrine\ORM\OptimisticLockException;
use FatchipAfterbuy\Services\Helper\ShopwareArticleHelper;
use FatchipAfterbuy\Services\WriteData\AbstractWriteDataService;
use FatchipAfterbuy\Services\WriteData\WriteDataInterface;
use FatchipAfterbuy\ValueObjects\Article as ValueArticle;
use Shopware\Models\Article\Article as ShopwareArticle;
use Shopware\Models\Article\Detail as ArticleDetail;
use Shopware\Models\Customer\Group as CustomerGroup;


class WriteProductsService extends AbstractWriteDataService implements WriteDataInterface
{

    /**
     * @param array $data
     */
    public function put(array $data)
    {
        $this->transform($data);
        $this->send($data);
    }

    /**
     * transforms valueObject into final structure for storage
     * could may be moved into separate helper
     *
     * @param ValueArticle[] $valueArticles
     */
    public function transform(array $valueArticles)
    {
        /**
         * @var CustomerGroup $customerGroup
         */
        $customerGroup = $this->entityManager->getRepository(CustomerGroup::class)->findOneBy(
            array('id' => $this->config['customerGroup'])
        );
        $netInput = $customerGroup->getTaxInput();

        if ( ! $customerGroup) {
            $this->logger->error('Target customer group not set', array('Import', 'Articles'));

            return;
        }

        /**
         * @var ShopwareArticleHelper $helper
         */
        $helper = $this->helper;

        foreach ($valueArticles as $valueArticle) {

            /**
             * @var ShopwareArticle $shopwareArticle
             */
            try {
                $shopwareArticle = $helper->getMainArticle(
                    $valueArticle->getExternalIdentifier(),
                    $valueArticle->getName(),
                    $valueArticle->getMainArticleId()
                );
            } catch (OptimisticLockException $e) {
                // TODO: correct error handling. This is NOT the correct place to handle such kind of errors. This should be done directly where the flush takes place
            }

            if ( ! $shopwareArticle) {
                continue;
            }

            /**
             * @var ArticleDetail $articleDetail
             */
            $articleDetail = $helper->getDetail($valueArticle->getExternalIdentifier(), $shopwareArticle);

            //set main values
            $articleDetail->setLastStock($valueArticle->getStockMin());
            $shopwareArticle->setName($valueArticle->getName());
            $shopwareArticle->setDescriptionLong($valueArticle->getDescription());
            $articleDetail->setInStock($valueArticle->getStock());
            $articleDetail->setEan($valueArticle->getEan());

            if($valueArticle->isActive()) {
                $articleDetail->setActive(1);
                $shopwareArticle->setActive(true);
            }

            if ($netInput && $valueArticle->getTax()) {
                $price = $valueArticle->getPrice() / (1 + ($valueArticle->getTax() / 100));
            } else {
                $price = $valueArticle->getPrice();
            }

            $helper->storePrices($articleDetail, $customerGroup, $price);

            $shopwareArticle->setSupplier($helper->getSupplier($valueArticle->getManufacturer()));

            // $attr = $helper->getArticleAttributes($shopwareArticle, $articleDetail, $valueArticle->getMainArticleId());

            $shopwareArticle->setTax($helper->getTax($valueArticle->getTax()));

            $helper->assignVariants($shopwareArticle, $articleDetail, $valueArticle->variants);

            $this->entityManager->persist($shopwareArticle);

            //have to flush cuz parent is not getting found otherwise
            try {
                $this->entityManager->flush();
            } catch (OptimisticLockException $e) {
            }

        }
    }


    /**
     * @param $targetData
     */
    public function send($targetData)
    {
        // TODO: necessary? We flush already earlier
        try {
            $this->entityManager->flush();
        } catch (OptimisticLockException $e) {
            // TODO: handle error
        }

        //TODO: update modDate
    }
}
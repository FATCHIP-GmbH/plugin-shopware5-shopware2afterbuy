<?php

namespace FatchipAfterbuy\Services\WriteData\Internal;

use FatchipAfterbuy\Services\Helper\ShopwareArticleHelper;
use FatchipAfterbuy\Services\WriteData\AbstractWriteDataService;
use FatchipAfterbuy\Services\WriteData\WriteDataInterface;
use Shopware\Models\Article\Detail;
use Shopware\Models\Customer\Group;


class WriteProductsService extends AbstractWriteDataService implements WriteDataInterface {

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
     * @return array
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function transform(array $data) {
        /**
         * @var Group $customerGroup
         */
        $customerGroup = $this->entityManager->getRepository('\Shopware\Models\Customer\Group')->findOneBy(array('id' => $this->config["customerGroup"]));
        $netInput = $customerGroup->getTaxInput();

        if(!$customerGroup) {
            $this->logger->error('Target customer group not set', array('Import', 'Articles'));
            return array();
        }

        /**
         * @var ShopwareArticleHelper $helper
         */
        $helper = $this->helper;

        /**
         * @var \Shopware\Models\Article\Article $article
         */

        foreach($data as $value) {
            /**
             * @var \FatchipAfterbuy\ValueObjects\Article $value
             */

            $article = $helper->getMainArticle($value->getExternalIdentifier(), $value->getName(), $value->getMainArticleId());

            if(!$article) {
                continue;
            }

            /**
             * @var Detail $detail
             */
            $detail = $helper->getDetail($value->getExternalIdentifier(), $article);

            //set main values
            $detail->setLastStock($value->getStockMin());
            $article->setName($value->getName());
            $article->setDescriptionLong($value->getDescription());
            $detail->setInStock($value->getStock());

            if($netInput && $value->getTax()) {
                $price = $value->getPrice() / (1+ ($value->getTax() / 100));
            }
            else {
                $price = $value->getPrice();
            }

            $helper->storePrices($detail, $customerGroup, $price);

            $article->setSupplier($helper->getSupplier($value->getManufacturer()));

            $attr = $helper->getArticleAttributes($article, $detail, $value->getMainArticleId());

            $article->setTax($helper->getTax($value->getTax()));

            $helper->assignVariants($article, $detail, $value->variants);

            $this->entityManager->persist($article);

            //have to flush cuz parent is not getting found otherwise
            $this->entityManager->flush();

        }
        return array();
    }


    /**
     * @param $targetData
     * @return mixed|void
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function send($targetData) {
       $this->entityManager->flush();

       //TODO: update modDate
    }
}
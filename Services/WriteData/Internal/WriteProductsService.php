<?php

namespace FatchipAfterbuy\Services\WriteData\Internal;

use FatchipAfterbuy\Services\Helper\ShopwareArticleHelper;
use FatchipAfterbuy\Services\Helper\ShopwareOrderHelper;
use FatchipAfterbuy\Services\WriteData\AbstractWriteDataService;
use FatchipAfterbuy\Services\WriteData\WriteDataInterface;
use FatchipAfterbuy\ValueObjects\Order;
use Shopware\Components\Api\Manager;
use Shopware\Components\Api\Resource\Article;
use Shopware\Models\Article\Detail;
use Shopware\Models\Article\Price;
use Shopware\Models\Shop\Shop;

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
     * @return mixed|void
     */
    public function transform(array $data) {
        //TODO: get from config
        $customerGroup = $this->entityManager->getRepository('\Shopware\Models\Customer\Group')->findOneBy(array('key' => 'EK'));



            /*'variants' => array(
                array(
                    'isMain' => true,
                    'number' => 'swTEST' . uniqid(),
                    'inStock' => 15,
                    'additionaltext' => 'S / Schwarz',
                    'configuratorOptions' => array(
                        array('group' => 'Size', 'option' => 'S'),
                        array('group' => 'Color', 'option' => 'Black'),
                    ),
                    'prices' => array(
                        array(
                            'customerGroupKey' => 'EK',
                            'price' => 999,
                        ),
                    )
                ),

            ),*/

        /**
         * @var \Shopware\Models\Article\Article $article
         */


        //TODO: set / create configurator group
        //TODO: set create configurator options


        //TODO: price net or brut based on customer group

        foreach($data as $value) {
            /**
             * @var \FatchipAfterbuy\ValueObjects\Article $value
             */

            $article = $this->helper->getMainArticle($value->getExternalIdentifier(), $value->getMainArticleId());

            /**
             * @var Detail $detail
             */
            $detail = $this->helper->getDetail($value->getExternalIdentifier(), $article);

            //set main values
            $detail->setLastStock($value->getStockMin());
            $article->setName($value->getName());

            $this->helper->storePrices($detail, $customerGroup, $value->getPrice());

            $article->setSupplier($this->helper->getSupplier($value->getManufacturer()));

            $attr = $this->helper->getArticleAttributes($article, $detail);

            $article->setTax($this->helper->getTax($value->getTax()));


            $this->entityManager->persist($article);

            $groups = $this->helper->getAssignableConfiguratorGroups($value->getVariants());

            $i++;

            /**
             * @var \Shopware\Models\Article\Article $article
             */


        }
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
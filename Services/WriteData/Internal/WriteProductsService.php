<?php

namespace FatchipAfterbuy\Services\WriteData\Internal;

use FatchipAfterbuy\Services\Helper\ShopwareOrderHelper;
use FatchipAfterbuy\Services\WriteData\AbstractWriteDataService;
use FatchipAfterbuy\Services\WriteData\WriteDataInterface;
use FatchipAfterbuy\ValueObjects\Order;
use Shopware\Components\Api\Manager;
use Shopware\Components\Api\Resource\Article;
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
        //return $this->send($data);
    }

    /**
     * transforms valueObject into final structure for storage
     * could may be moved into separate helper
     *
     * @param array $data
     * @return mixed|void
     */
    public function transform(array $data) {
        //$article = new $this->targetRepository();

        $_Article = array(
            'name' => 'ConfiguratorTest',
            'description' => 'A test article',
            'descriptionLong' => '<p>I\'m a <b>test article</b></p>',
            'active' => true,
            'tax' => 19.00,
            'supplier' => '',

            //TODO: only set if main
            'mainDetail' => array(
                'number' => 'swTEST' . uniqid(),
                'active' => true,
                'laststock' => 0,
                'prices' => array(
                    array(
                        'customerGroupKey' => 'EK',
                        'price' => 999,
                    ),
                )
            ),

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
        );
        /**
         * @var \Shopware\Models\Article\Article $article
         */

        //$article = $article->fromArray($_Article);

        //TODO: set / create configurator group
        //TODO: set create configurator options
        //TODO: set / create supplier

        //TODO: assign customergroup
        //TODO: assign pricegroupid
        //TODO: price net or brut based on customer group

      /*  $article->setTax($this->helper->getTax($_Article["tax"]));

        $article->setPriceGroup($this->entityManager->find('\Shopware\Models\Price\Group', 1));
        $article->setSupplier($this->entityManager->find('\Shopware\Models\Article\Supplier', 1));
        //$article->getMainDetail()->setArticle($article);
        $prices = $article->getMainDetail()->getPrices();

        $article->getMainDetail()->setAttribute(new \Shopware\Models\Attribute\Article());

        foreach($prices as $price) {

            $price->setArticle($article);
            $price->setCustomerGroup($this->entityManager->getRepository('\Shopware\Models\Customer\Group')->findOneBy(array('key' => 'EK')));
        }


*/


        foreach($data as $value) {
            //TODO: get article

            $article = $this->helper->getMainArticle($value->externalIdentifier);

            //TODO: get detail
            $detail = $this->helper->getDetail($value->externalIdentifier, $article);

            //set main values
            $detail->setLastStock(0);

            //TODO: price + group + tax

            //TODO: supplier
            $article->setSupplier($this->helper->getSupplier('Hersteller2'));


            $this->entityManager->persist($article);
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
    }
}
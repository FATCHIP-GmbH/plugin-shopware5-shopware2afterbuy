<?php

namespace FatchipAfterbuy\Services\WriteData\Internal;

use FatchipAfterbuy\Services\Helper\ShopwareOrderHelper;
use FatchipAfterbuy\Services\WriteData\AbstractWriteDataService;
use FatchipAfterbuy\Services\WriteData\WriteDataInterface;
use FatchipAfterbuy\ValueObjects\Order;
use Shopware\Components\Api\Resource\Article;
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
        $article = new $this->targetRepository();

        $minimalTestArticle = array(
            'name' => 'Sport Shoes',
            'active' => true,
            'taxId' => 1,
            'supplier' => 'Sport Shoes Inc.',
            'mainDetail' => array(
                'number' => 'turn',
                'active' => true,
                'laststock' => 0,
                'prices' => array(
                    array(
                        'customerGroupKey' => 'EK',
                        'price' => 999,
                    ),
                )
            ),
        );
        /**
         * @var \Shopware\Models\Article\Article $article
         */

        $article = $article->fromArray($minimalTestArticle);

        //TODO: assign tax
        //TODO: assign customergroup
        //TODO: assign pricegroupid

        $article->setTax($this->entityManager->find('\Shopware\Models\Tax\Tax', 1));
        $article->setPriceGroup($this->entityManager->find('\Shopware\Models\Price\Group', 1));
        $article->setSupplier($this->entityManager->find('\Shopware\Models\Article\Supplier', 1));



/*        $violations = $this->getManager()->validate($article);
        if ($violations->count() > 0) {
            throw new ApiException\ValidationException($violations);
        }*/

        Shopware()->Models()->persist($article);
        Shopware()->Models()->flush();

        //foreach($data as $value) {
            //log and ignore order if country is not setup in shop
            //TODO: use entity component

            /**
             * @var \Shopware\Models\Article\Article $article
             */


        //}
    }


    /**
     * @param $targetData
     * @return mixed|void
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function send($targetData) {
       // $this->entityManager->flush();
    }
}
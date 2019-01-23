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

class ReadProductsService extends AbstractReadDataService implements ReadDataInterface {

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
        if($this->targetEntity === null) {
            return array();
        }

        $targetData = array();

        foreach($data as $entity) {

            /**
             * @var \Shopware\Models\Article\Article $entity
             */

            if(empty($entity)) {
                continue;
            }

            /**
             * @var Article $article
             */
            $article = new $this->targetEntity();

            $article->setActive($entity->getActive());
            $article->setName($entity->getName());


            $article->setDescription($entity->getDescription());
            $article->setTax($entity->getTax()->getTax());

            $article->setManufacturer($entity->getSupplier()->getName());

            if(!$entity->getConfiguratorSet()) {
                //simple article

                $detail = $entity->getMainDetail();

                if($detail->getEan()) {
                    $article->setEan($detail->getEan());
                }
                $article->setInternalIdentifier($detail->getNumber());
                $article->setStockMin($detail->getStockMin());
                $article->setStock($detail->getInStock());

                //TODO: get correct price for customergroup
                //TODO: brut net calc in separate method

                $article->setPrice($detail->getPrices()->first()->getPrice());

                //TODO: set afterbuy id if existing
                //$article->setExternalIdentifier();

                $article->setVariantArticles(null);
            }
            else {
                //variant article
            }

            $targetData[] = $article;


            //if no variant article -> set here

            //TODO: for variants
           /* $article->setEan();
            $article->setInternalIdentifier();
            $article->setStockMin();
            $article->setStock();
            $article->setPrice();
            $article->setExternalIdentifier();
            $article->setPseudoPrice();
            $article->setVariantArticles();*/





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
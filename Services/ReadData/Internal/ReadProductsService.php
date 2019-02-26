<?php

namespace viaebShopwareAfterBuy\Services\ReadData\Internal;

use Fatchip\Afterbuy\ApiClient;
use viaebShopwareAfterBuy\Components\Helper;
use viaebShopwareAfterBuy\Services\Helper\ShopwareArticleHelper;
use viaebShopwareAfterBuy\Services\ReadData\AbstractReadDataService;
use viaebShopwareAfterBuy\Services\ReadData\ReadDataInterface;
use viaebShopwareAfterBuy\ValueObjects\Address;
use viaebShopwareAfterBuy\ValueObjects\Article;
use viaebShopwareAfterBuy\ValueObjects\Order;
use viaebShopwareAfterBuy\ValueObjects\OrderPosition;
use viaebShopwareAfterBuy\ValueObjects\ProductPicture;
use Shopware\Models\Article\Configurator\Option;
use Shopware\Models\Article\Price;
use Shopware\Models\Category\Category;
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
        $this->logger->debug('Receiving products from shop', $data);

        /**
         * @var ShopwareArticleHelper $helper
         */
        $helper = $this->helper;

        if($this->targetEntity === null) {
            return array();
        }

        $this->customerGroup = $helper->getDefaultCustomerGroup($this->config['customerGroup']);
        $netInput = $this->customerGroup->getTaxInput();

        $targetData = array();

        foreach($data as $entity) {

            if(empty($entity) || is_null($entity->getTax())) {
                continue;
            }

            /** @var \Shopware\Models\Article\Article $entity */
            $article = $helper->setArticleMainValues($entity, $this->targetEntity);
            $helper->assignCategories($article, $entity);
            $helper->assignArticleImages($entity, $article);

            if(!$entity->getConfiguratorSet()) {
                //simple article
                $helper->setSimpleArticleValues($entity, $article, $netInput);
            }
            else {
                $article->setInternalIdentifier('AB' . $entity->getMainDetail()->getNumber());

                foreach ($entity->getDetails() as $detail) {

                    if($detail->getAttribute() === null) {
                        $this->helper->fixMissingAttribute($detail);
                    }

                    $variant = $helper->setVariantValues($entity, $detail, $this->targetEntity, $netInput);

                    $helper->assignArticleImages($entity, $variant, $detail);
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

        $data = $this->helper->getUnexportedArticles($filter['submitAll'], $this->config['ExportAllArticles']);

        if(!$data || empty($data)) {
            return array();
        }

        return $data;
    }
}
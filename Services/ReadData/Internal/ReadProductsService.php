<?php

namespace viaebShopwareAfterbuy\Services\ReadData\Internal;

use Shopware\Models\Article\Article;
use viaebShopwareAfterbuy\Services\Helper\ShopwareArticleHelper;
use viaebShopwareAfterbuy\Services\ReadData\AbstractReadDataService;
use viaebShopwareAfterbuy\Services\ReadData\ReadDataInterface;

/**
 * Class ReadProductsService
 * @package viaebShopwareAfterbuy\Services\ReadData\Internal
 * @property ShopwareArticleHelper $helper
 */
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
     */
    public function transform(array $data) {
        $this->logger->debug('Receiving products from shop', $data);

        if($this->targetEntity === null) {
            return array();
        }

        $this->customerGroup = $this->helper->getDefaultCustomerGroup($this->config['customerGroup']);

        if($this->customerGroup === null) {
            $this->logger->error('Default customer group not defined');
            exit('Default customer group not defined');
        }

        $netInput = $this->customerGroup->getTaxInput();

        $targetData = array();

        foreach($data as $entity) {

            if(empty($entity)) {
                $this->logger->error('Empty article object');
                continue;
            }

            if($entity->getTax() === null || $entity->getMainDetail() === null) {
                $this->logger->error('Invalid article', array('article' => $entity->getId()));
                continue;
            }

            /** @var Article $entity */
            $article = $this->helper->setArticleMainValues($entity, $this->targetEntity);
            $this->helper->assignCategories($article, $entity);
            $this->helper->assignArticleImages($entity, $article);

            if(!$entity->getConfiguratorSet()) {
                //simple article
                $this->helper->setSimpleArticleValues($entity, $article, $netInput);
            }
            else {
                $article->setInternalIdentifier('AB' . $entity->getMainDetail()->getNumber());

                foreach ($entity->getDetails() as $detail) {

                    if($detail->getAttribute() === null) {
                        $this->helper->fixMissingAttribute($detail);
                    }

                    $variant = $this->helper->setVariantValues($entity, $detail, $this->targetEntity, $netInput);

                    $this->helper->assignArticleImages($entity, $variant, $detail);

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

        $data = $this->helper->getUnexportedArticles($filter['submitAll'], (int)$this->config['ExportAllArticles']);

        if(!$data || empty($data)) {
            return array();
        }

        return $data;
    }
}
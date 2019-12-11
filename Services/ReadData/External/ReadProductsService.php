<?php
/** @noinspection SpellCheckingInspection */

namespace viaebShopwareAfterbuy\Services\ReadData\External;

use Fatchip\Afterbuy\ApiClient;
use viaebShopwareAfterbuy\Services\Helper\AfterbuyProductsHelper;
use viaebShopwareAfterbuy\Services\ReadData\AbstractReadDataService;
use viaebShopwareAfterbuy\Services\ReadData\ReadDataInterface;
use viaebShopwareAfterbuy\ValueObjects\Article as ValueArticle;

/**
 * Class ReadProductsService
 * @package viaebShopwareAfterbuy\Services\ReadData\External
 * @property AfterbuyProductsHelper $helper
 */
class ReadProductsService extends AbstractReadDataService implements ReadDataInterface
{
    /**
     * @param array $filter
     *
     * @return ValueArticle[]
     */
    public function get(array $filter)
    {
        $data = $this->read($filter);

        return $this->transform($data);
    }

    /**
     * transforms api input into ValueArticle (targetEntity)
     *
     * @param array $products
     *
     * @return ValueArticle[]
     */
    public function transform(array $products)
    {
        $this->logger->debug('Receiving products from afterbuy', $products);

         if ($this->targetEntity === null) {
            return array();
        }

        /** @var ValueArticle[] $valueArticles */
        $valueArticles = array();

        foreach ($products as $product) {

            if (empty($product)) {
                continue;
            }

            $valueArticle = $this->helper->createValueArticle($product, $this->targetEntity);

            //ignore product if article number is not valid
            if(empty($valueArticle->getOrdernunmber() || $valueArticle->getOrdernunmber() === 0 || $valueArticle->getOrdernunmber() === '0')) {
                continue;
            }

            $valueArticle = $this->helper->setDefaultArticleValues($valueArticle, $product);
            $this->helper->addProductPictures($product, $valueArticle);
            $valueArticle = $this->helper->addCatalogs($valueArticle, $product);
            $valueArticle = $this->helper->setVariants($valueArticle, $product);

            if(!$valueArticle->getMainArticleId()) {
                $valueArticles[] = $valueArticle;
            }
            else {
                array_unshift($valueArticles, $valueArticle);
            }
        }

        return $valueArticles;
    }


    /**
     * provides api data. dummy data as used here can be used in tests
     *
     * @param array $filter
     *
     * @return array
     */
    public function read(array $filter)
    {
        $resource = new ApiClient($this->apiConfig, $this->logger);
        $data = $resource->getAllShopProductsFromAfterbuy($filter);

        if ( ! $data || empty($data)) {
            return array();
        }

        return $data;
    }
}
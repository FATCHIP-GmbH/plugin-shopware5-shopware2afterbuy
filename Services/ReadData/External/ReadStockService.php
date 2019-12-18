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
class ReadStockService extends AbstractReadDataService implements ReadDataInterface
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
        $this->logger->debug('Receiving stock from afterbuy', $products);

         if ($this->targetEntity === null) {
            return array();
        }

        /** @var ValueArticle[] $valueArticles */
        $valueArticles = array();

        foreach ($products as $product) {

            if (empty($product)) {
                continue;
            }

            $valueArticles[] = new $this->targetEntity($product["ProductID"], intval($product["Quantity"]));
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
<?php
/** @noinspection SpellCheckingInspection */

namespace viaebShopwareAfterbuy\Services\ReadData\External;

use Fatchip\Afterbuy\ApiClient;
use viaebShopwareAfterbuy\Services\Helper\AfterbuyProductsHelper;
use viaebShopwareAfterbuy\Services\ReadData\AbstractReadDataService;
use viaebShopwareAfterbuy\Services\ReadData\ReadDataInterface;
use viaebShopwareAfterbuy\ValueObjects\Stock as ArticleStock;

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
     * @return ArticleStock[]
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
     * @return ArticleStock[]
     */
    public function transform(array $products)
    {
        $this->logger->debug('Receiving stock from afterbuy', $products);

         if ($this->targetEntity === null) {
            return array();
        }

        /** @var ArticleStock[] $articleStocks */
        $articleStocks = array();

        foreach ($products as $product) {

            if (empty($product)) {
                continue;
            }

            if((int)$this->config['ordernumberMapping'] === 1) {
                $articleIdentifier = $product['Anr'];
            } else {
                $articleIdentifier = $product["ProductID"];
            }

            $articleStocks[] = new $this->targetEntity($articleIdentifier, intval($product["Quantity"]));
        }

        return $articleStocks;
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
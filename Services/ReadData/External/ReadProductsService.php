<?php

namespace viaebShopware2Afterbuy\Services\ReadData\External;

use Fatchip\Afterbuy\ApiClient;
use viaebShopware2Afterbuy\Components\Helper;
use viaebShopware2Afterbuy\Services\Helper\AfterbuyProductsHelper;
use viaebShopware2Afterbuy\Services\ReadData\AbstractReadDataService;
use viaebShopware2Afterbuy\Services\ReadData\ReadDataInterface;
use viaebShopware2Afterbuy\ValueObjects\Article as ValueArticle;

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

            /**
             * @var ValueArticle $valueArticle
             */
            $valueArticle = new $this->targetEntity();
            $valueArticle->setEan($product['EAN']);
            $valueArticle->setName($product['Name']);
            $valueArticle->setExternalIdentifier($product['ProductID']);
            $valueArticle->setPrice(Helper::convertDeString2Float($product['SellingPrice']));
            $valueArticle->setManufacturer($product['ProductBrand']);
            $valueArticle->setStock($product['Quantity']);
            $valueArticle->setStockMin((int)$product['MinimumStock']);
            $valueArticle->setTax(Helper::convertDeString2Float($product['TaxRate']));
            $valueArticle->setDescription($product['Description']);

            /** @var AfterbuyProductsHelper $helper */
            $helper = $this->helper;
            $helper->addProductPictures($product, $valueArticle);

            // catalogs - categories
            if (array_key_exists('Catalogs', $product) && array_key_exists('CatalogID', $product['Catalogs'])) {
                $catalogIDs = $product['Catalogs']['CatalogID'];
                if ( ! is_array($catalogIDs)) {
                    $catalogIDs = [$catalogIDs];
                }

                $valueArticle->setExternalCategoryIds($catalogIDs);
            }

            if ((int)$product['Quantity'] > (int)$product['MinimumStock']) {
                $valueArticle->setActive(true);
            }

            $variants = [];

            if (!array_key_exists('Attributes', $product) && array_key_exists('BaseProducts', $product) && $product["BaseProductFlag"] != "1") {
                $valueArticle->setMainArticleId($product['BaseProducts']['BaseProduct']['BaseProductID']);

                $variants[] = array(
                    'option' => 'Variation',
                    'value'  => $product['Name'],
                );
            }

            if (array_key_exists('Attributes', $product) && array_key_exists('BaseProducts', $product) && $product["BaseProductFlag"] != "1") {
                $valueArticle->setMainArticleId($product['BaseProducts']['BaseProduct']['BaseProductID']);


                if (array_key_exists('AttributName', $product['Attributes']['Attribut'])) {
                    $variants[] = array(
                        'option' => $product['Attributes']['Attribut']['AttributName'],
                        'value'  => $product['Attributes']['Attribut']['AttributValue'],
                    );
                } else {
                    $variants = [];

                    foreach ($product['Attributes']['Attribut'] as $option) {
                        $variant = array(
                            'option' => $option['AttributName'],
                            'value'  => $option['AttributValue'],
                        );

                        $variants[] = $variant;
                    }
                }
            }

            if ( ! empty($variants) && $product["BaseProductFlag"] != "1") {
                $valueArticle->setVariants($variants);
            }

            $valueArticles[] = $valueArticle;
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
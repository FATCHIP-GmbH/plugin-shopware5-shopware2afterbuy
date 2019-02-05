<?php

namespace FatchipAfterbuy\Services\ReadData\External;

use Fatchip\Afterbuy\ApiClient;
use FatchipAfterbuy\Components\Helper;
use FatchipAfterbuy\Services\Helper\AfterbuyProductsHelper;
use FatchipAfterbuy\Services\ReadData\AbstractReadDataService;
use FatchipAfterbuy\Services\ReadData\ReadDataInterface;
use FatchipAfterbuy\ValueObjects\Article as ValueArticle;

class ReadProductsService extends AbstractReadDataService implements ReadDataInterface
{

    /**
     * @param array $filter
     *
     * @return ValueArticle[]
     */
    public function get(array $filter): array
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
    public function transform(array $products): array
    {
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

            if ((int)$product['Quantity'] > (int)$product['MinimumStock']) {
                $valueArticle->setActive(true);
            }


            if (array_key_exists('Attributes', $product) && array_key_exists('BaseProducts', $product)) {
                $valueArticle->setMainArticleId($product['BaseProducts']['BaseProduct']['BaseProductID']);


                if (array_key_exists('AttributName', $product['Attributes']['Attribut'])) {
                    $variants = array(
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

                if ( ! empty($variants)) {
                    $valueArticle->setVariants($variants);
                }
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
    public function read(array $filter): array
    {

        $resource = new ApiClient($this->apiConfig);
        $data = $resource->getAllShopProductsFromAfterbuy($filter);

        if ( ! $data || empty($data)) {
            return array();
        }

        return $data;
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 10.08.18
 * Time: 10:09
 */

namespace Shopware\FatchipShopware2Afterbuy\Components;


class ProductsToArticlesConverter {
    /** @var array $articles */
    protected $articles;
    /** @var array $details */
    protected $details;
    /** @var array $mainDetails */
    protected $mainDetails;

    /**
     * ProductsToArticlesConverter constructor.
     */
    public function __construct() {
        // $this->articles = [
        //     'configuratorSet' => [
        //         'groups' => [],
        //     ],
        // ];
        $this->articles = [];
        $this->details = [];
        $this->mainDetails = [];
    }


    /**
     * Converts products array to articles array.
     *
     * @param array $products
     *
     * @return array
     */
    public function convertProducts2Articles($products) {
        // for each product in products
        foreach ($products as $product) {
            // Map article / detail field names

            $productID = $product['ProductID'];

            // variantSet related?
            if (isset($product['BaseProducts'])) {
                // variantSet parent object?
                if ( ! isset($product['BaseProducts']['BaseProduct']['BaseProductID'])) {

                    $variantSets[$productID] = $product;

                    $this->mapArticleData($product);

                    $children = $product['BaseProducts']['BaseProduct'];

                    // foreach variant set product
                    foreach ($children as $child) {
                        $this->processChild($child, $productID);
                    }
                } // variantSet childObject
                else {
                    $parentProductID = $product
                    ['BaseProducts']
                    ['BaseProduct']
                    ['BaseProductID'];

                    $this->mapDetailData($product);

                    // variant set already processed?
                    if (isset($this->articles[$parentProductID])) {
                        $this->addDetailToArticle(
                            $parentProductID,
                            $productID,
                            $this->mainDetails[$parentProductID]
                            == $productID
                        );
                    }
                }
            } // single product
            else {
                $this->mapDetailData($product);

                $this->mapArticleData($product);

                $this->addDetailToArticle($productID, $productID, true);
            }
        }

        return $this->articles;
    }

    /**
     * @param $child
     * @param $parentID
     */
    protected function processChild($child, $parentID) {
        $childID = $child['BaseProductID'];

        // is childProduct the mainDetail for parentProduct?
        if ($child['BaseProductsRelationData']['DefaultProduct'] == -1) {
            $this->mainDetails[$parentID] = $childID;
        }

        //find variation groups and options
        // $options = $child['BaseProductsRelationData']['eBayVariationData'];
        // foreach ($options as $option) {
        //     $this->addOption($option);
        // }

        // detail already processed?
        if (isset($this->details[$childID])) {
            $this->addDetailToArticle(
                $parentID,
                $childID,
                $this->mainDetails[$parentID] == $childID
            );
        }
    }

    /**
     * Converts the given product array to an article array, by mapping the
     * relevant fields. The article will be added to global articles mapped by
     * ProductID
     *
     * @param array $product - Array with product data, as it comes from the
     *                       Afterbuy API.
     */
    protected function mapArticleData($product) {
        // https://community.shopware.com/Artikel-anlegen_detail_807.html
        // https://community.shopware.com/_detail_1778.html
        $this->articles[$product['ProductID']] = [
            'name'             => $product['Name'],
            'description'      => $product['ShortDescription'],
            'descriptionLong'  => $product['Description'],
            // TODO: not in article model, but in db
            'shippingtime'     => $product['DeliveryTime'],
            'tax'              => $product['TaxRate'],
            'keywords'         => $product['Keywords'],
            'changed'          => $product['ModDate'],
            'active'           => 1,
            'pseudoSales'      => 0,
            'highlight'        => false,
            'metaTitle'        => '',
            'lastStock'        => $product['Discontinued'] & $product['Stock'],
            'notification'     => false,
            'template'         => '',
            'supplier'         => $product['ProductBrand'],
            'availableFrom'    => null,
            'availableTo'      => null,
            'configuratorSet'  => null,
            'priceGroup'       => null,
            'pricegroupActive' => false,
            'propertyGroup'    => null,
            'crossBundleLook'  => false,

            // TODO: what to map here?

            // could not find field in AB API
            'added'            => null,
            // not sure what kind of mode is meant
            'mode'             => 0,
            'variants'         => [],
        ];
    }

    /**
     * Converts the given product array to detail array, by mapping the relevant
     * fields. The given product must be variantSet related, therefore
     * $product['BaseProductsRelationData'] must be set.
     *
     * @param array $product - Array with product data, as it comes from the
     *                       Afterbuy API.
     */
    protected function mapDetailData($product) {
        $ordernumberMapping = Shopware()
            ->Models()
            ->getRepository(
                'Shopware\CustomModels\FatchipShopware2Afterbuy\PluginConfig'
            )
            ->findOneBy(['id' => '1'])
            ->getOrdernumberMapping();

        // is ordernumberMapping in special case EuAN?
        if ($ordernumberMapping === 'EuAN') {
            if ($product['ManufacturerStandardProductIDType'] === 'EAN') {
                $ordernumberMapping = 'ManufacturerStandardProductIDValue';
            }
            // else $ordernumberMapping will stay on 'EuAN'
            // then $detail['number'] will be null
        }

        $this->details[$product['ProductID']] = [
            'number'         => $product[$ordernumberMapping],
            'supplierNumber' => $product['ManufacturerPartNumber'],
            'active'         => true,
            'inStock'        => $product['Quantity'],
            'stockMin'       => $product['MinimumStock'],
            'lastStock'      => $product['Discontinued'] & $product['Stock'],
            'weight'         => $product['Weight'],
            'ean'            =>
                $product['ManufacturerStandardProductIDType'] == 'EAN'
                    ? $product['ManufacturerStandardProductIDValue']
                    : null,
            'unit'           => $product['UnitOfQuantity'],
            'prices'         => [
                [
                    'customerGroupKey' => 'EK',
                    'price'            => $product['SellingPrice'],
                ],
            ],
            'additionalText' => '',

            // TODO: not in article model, but in db
            'sales'          => '',

            // TODO: what to map here
            'position'       => $product['Position'],
            'width'          => null,
            'height'         => null,
            'len'            => null,
            'purchaseSteps'  => '',
            'maxPurchase'    => '',
            'minPurchase'    => '',
            'purchaseUnit'   => '',
            'referenceUnit'  => '',
            'packUnit'       => '',
            'releaseDate'    => '',
            'shippingFree'   => '',
            'shippingTime'   => $product['DeliveryTime'],
            'purchasePrice'  => '',
        ];
    }

    /**
     * Adds the detail with the given childID to the article with the given
     * parentID. When detail is mainDetail, the detail is set to article's
     * mainDetail field. Otherwise the detail is added to the variants array.
     *
     * @param $parentID
     * @param $childID
     * @param $isMainDetail
     */
    protected function addDetailToArticle($parentID, $childID, $isMainDetail) {
        if ($isMainDetail) {
            // add detail as mainDetail
            $this->articles[$parentID]['mainDetail'] = $this->details[$childID];
        } else {
            // add detail as variant
            $this->articles[$parentID]['variants'][] = $this->details[$childID];
        }
    }

    /**
     * @param $option
     */
    protected function addOption($option) {
        // variationGroup missing in map?
        if ( ! array_key_exists(
            $option['ebayVariationName'],
            $this->articles['configuratorSet']['groups']
        )
        ) {
            // create new group
            $articles['configuratorSet']['groups'][$option['ebayVariationName']] =
                [];
        }

        // variationOption missing in Group?
        if ( ! in_array(
            $option['ebayVariationValue'],
            $this->articles['configuratorSet']['groups'][$option['ebayVariationName']]
        )
        ) {
            // add option to group
            $this->articles['configuratorSet']['groups'][$option['ebayVariationName']][] =
                $option['ebayVariationValue'];
        }
    }
}

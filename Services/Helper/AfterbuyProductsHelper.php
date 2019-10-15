<?php
/** @noinspection SpellCheckingInspection */

namespace viaebShopwareAfterbuy\Services\Helper;

use Exception;
use Fatchip\Afterbuy\ApiClient;
use viaebShopwareAfterbuy\ValueObjects\Article;
use viaebShopwareAfterbuy\ValueObjects\Article as ValueArticle;
use viaebShopwareAfterbuy\ValueObjects\ProductPicture;
use viaebShopwareAfterbuy\Components\Helper;

/**
 * Class ShopwareArticleHelper
 * @package viaebShopwareAfterbuy\Services\Helper
 */
class AfterbuyProductsHelper extends ShopwareArticleHelper
{
    /**
     * @param array $images
     * @return array
     */
    public function buildAfterbuyImages(array $images)
    {
        $productPictures = [];

        for($i = 1; $i <= 12; $i++) {

            $index = $i - 1;

            if(!array_key_exists($index, $images) || $images[$index] === null) {
                $imageUrl = '';
                $imageAltText = '';
            } else {
                $imageUrl = $images[$index]->getUrl();
                $imageAltText = $images[$index]->getAltText();
            }

            $productPictures[] = array(
                'ProductPicture' => array(
                    'Nr' => $i,
                    'Url' => $imageUrl,
                    'AltText' => $imageAltText
                )
            );
        }

        return $productPictures;
    }

    /**
     * @param array $data
     * @param ApiClient $api
     * @param array $afterbuyProductIds
     * @return array
     */
    public function submitAfterbuyVariantProducts(array $data, ApiClient $api, $afterbuyProductIds = [])
    {
        foreach ($data as $value) {

            if (!$value->getVariantArticles()) {
                continue;
            }

            $products = array(
                'Products' => array()
            );

            foreach ($value->getVariantArticles() as $variant) {
                $variant = $this->buildAfterbuyVariant($variant, $value);
                $products['Products'][] = $variant;
            }

            $this->sendAfterbuyProducts($products, $api, $afterbuyProductIds);
            $baseProduct = $this->buildAfterbuyVariantBaseProduct($value, $afterbuyProductIds);
            $this->sendAfterbuyProducts($baseProduct, $api);
        }

        return $afterbuyProductIds;
    }

    /**
     * @param Article $variant
     * @param Article $value
     * @return array
     */
    public function buildAfterbuyVariant(Article $variant, Article $value)
    {
        $variants = $this->buildAfterbuyVariantOptions($variant);
        $variantImages = $this->buildAfterbuyImages($variant->getProductPictures());

        $product = array(
            'Product' => array(
                'ProductIdent' => array(
                    'ProductInsert' => 1,
                    'Anr' => $variant->getVariantId()
                ),
                'Anr' => $variant->getVariantId(),
                'EAN' => $variant->getInternalIdentifier(),
                'Name' => $variant->getName(),
                'ManufacturerPartNumber' => $variant->getSupplierNumber(),
                'Description' => $variant->getDescription(),
                'ShortDescription' => $variant->getShortDescription(),
                'Keywords' => $variant->getKeywords(),
                'Weight' => Helper::convertNumberToABString($variant->getWeight()),
                'Quantity' => $variant->getStock(),
                'UnitOfQuantity' => 'Stk',
                'MinimumStock' => $variant->getStockMin(),
                'BuyingPrice' => Helper::convertNumberToABString($variant->getBuyingPrice()),
                'SellingPrice' => Helper::convertNumberToABString($variant->getPrice()),
                'TaxRate' => Helper::convertNumberToABString($variant->getTax()),
                'ProductBrand' => $value->getManufacturer(),
                'AddAttributes' => array(
                    'UpdateAction' => 3,
                    'AddAttribut' => $variants
                ),
                'ImageLargeURL' => $variant->getMainImageUrl(),
                'ImageSmallURL' => $variant->getMainImageThumbnailUrl(),
                'ProductPictures' => $variantImages,
                'Stock' => ($variant->isLastStock() === true) ? 1 : 0,
                'Discontinued' => ($variant->isLastStock() === true) ? 1 : 0,
                'BasepriceFactor' => $variant->getBasePriceFactor()
            )
        );

        return $product;
    }

    /**
     * @param Article $variant
     * @return array
     */
    public function buildAfterbuyVariantOptions(Article $variant)
    {
        $variants = [];

        foreach ($variant->getVariants() as $group => $option) {
            $variants[] = array(
                'AttributName' => $group,
                'AttributValue' => $option,
                'AttributTyp' => 1,
                'AttributRequired' => 1
            );
        }

        return $variants;
    }

    /**
     * @param Article $value
     * @param array $afterbuyProductIds
     * @return mixed
     */
    public function buildAfterbuyVariantBaseProduct(Article $value, array $afterbuyProductIds) {
        $variantArticles = $this->buildAfterbuyVariantAssignment($value, $afterbuyProductIds);
        $productImages = $this->buildAfterbuyImages($value->getProductPictures());

        // we have no unique base product identifier
        // Anr 1,{$articleId}
        // neccessary cuz articleIds and detailIds may collidate
        $products['Products'] = array(
            'Product' => array(
                'ProductIdent' => array(
                    'ProductInsert' => 1,
                    'Anr' => '1,' . $value->getMainArticleId(),
                    'BaseProductType' => 1
                ),
                'Anr' => '1,' . $value->getMainArticleId(),
                'EAN' => $value->getInternalIdentifier(),
                'Name' => $value->getName(),
                'Description' => $value->getDescription(),
                'ShortDescription' => $value->getShortDescription(),
                'Keywords' => $value->getKeywords(),
                'Weight' => Helper::convertNumberToABString($value->getWeight()),
                'UnitOfQuantity' => 'Stk',
                'TaxRate' => Helper::convertNumberToABString($value->getTax()),
                'ManufacturerPartNumber' => $value->getSupplierNumber(),
                'ProductBrand' => $value->getManufacturer(),
                'AddBaseProducts' => $variantArticles,
                'ImageLargeURL' => $value->getMainImageUrl(),
                'ImageSmallURL' => $value->getMainImageThumbnailUrl(),
                'ProductPictures' => $productImages,
                'AddCatalogs' => $this->buildAfterbuyCatalogAssignment($value->getExternalCategoryIds()),
                'Stock' => ($value->isLastStock() === true) ? 1 : 0,
                'Discontinued' => ($value->isLastStock() === true) ? 1 : 0,
            )
        );

        return $products;
    }

    /**
     * @param Article $value
     * @param array $afterbuyProductIds
     * @return array
     */
    public function buildAfterbuyVariantAssignment(Article $value, array $afterbuyProductIds)
    {
        $variantArticles = [];

        foreach($value->getVariantArticles() as $variant) {
            /** @var ValueArticle $variant */
            if(array_key_exists($variant->getVariantId(), $afterbuyProductIds)) {
                $variant->setExternalIdentifier($afterbuyProductIds[$variant->getVariantId()]);
            }

            if(!$variant->getExternalIdentifier()) {
                continue;
            }

            $variantArticles[] = array(
                'AddBaseProduct' => array(
                    'ProductID' => $variant->getExternalIdentifier(),
                    'ProductLabel' => $variant->getName(),
                    'ProductQuantity' => $variant->getStock()
                )
            );
        }

        return $variantArticles;
    }

    /**
     * @param $ids
     * @return array
     */
    public function buildAfterbuyCatalogAssignment($ids)
    {
        $catalogs = [];

        foreach($ids as $value) {
            $catalogs[] = array(
                    'CatalogID' => $value
            );
        }

        $assignment = array(
            'UpdateAction' => 2,
            'AddCatalog' => $catalogs
        );

        return $assignment;
    }

    /**
     * @param array $data
     * @param ApiClient $api
     * @param array $afterbuyProductIds
     * @return array
     */
    public function submitAfterbuySimpleProducts(array $data, ApiClient $api, $afterbuyProductIds = [])
    {
        $products = array(
            'Products' => array(
            )
        );

        foreach($data as $value) {
            /**
             * @var ValueArticle $value
             */

            if($value->getVariantArticles()) {
                continue;
            }

            $product = $this->buildAfterbuySimpleProduct($value);

            $products['Products'][] = $product;
        }

        $this->sendAfterbuyProducts($products, $api, $afterbuyProductIds);

        return $afterbuyProductIds;
    }

    /**
     * @param array $products
     * @param ApiClient $api
     * @param array $afterbuyProductIds
     */
    public function sendAfterbuyProducts(array $products, ApiClient $api, &$afterbuyProductIds = [])
    {
        if(count($products['Products'])) {

            try {
                $response = $api->updateShopProducts($products);
            }
            catch (Exception $e) {
                $this->logger->error($e->getMessage(), array($e->getFile(), $products));
                exit($e->getMessage());
            }

            if(array_key_exists('Result', $response) && array_key_exists('NewProducts', $response['Result'])) {

                if (array_key_exists('ProductID', $response['Result']['NewProducts']['NewProduct'])) {
                    $internalArticleNumber = $response['Result']['NewProducts']['NewProduct']['Anr'];
                    $afterbuyProductIds[$internalArticleNumber] = $response['Result']['NewProducts']['NewProduct']['ProductID'];
                } elseif (is_array($response['Result']['NewProducts']['NewProduct'][0])) {

                    foreach ($response['Result']['NewProducts']['NewProduct'] as $newProduct) {
                        $internalArticleNumber = $newProduct['Anr'];
                        $afterbuyProductIds[$internalArticleNumber] = $newProduct['ProductID'];
                    }
                }
            }
        }
    }

    /**
     * @param Article $value
     * @return array
     */
    public function buildAfterbuySimpleProduct(Article $value)
    {
        $product = array(
            'Product' => array(
                'ProductIdent' => array(
                    'ProductInsert' => 1,
                    'Anr' => $value->getVariantId()
                ),
                'EAN' => $value->getInternalIdentifier(),
                'Anr' => (string) $value->getVariantId(),
                'Name' => $value->getName(),
                'ManufacturerPartNumber' => $value->getSupplierNumber(),
                'Description' => $value->getDescription(),
                'ShortDescription' => $value->getShortDescription(),
                'Weight' => Helper::convertNumberToABString($value->getWeight()),
                'Quantity' => $value->getStock(),
                'UnitOfQuantity' => 'Stk',
                'MinimumStock' => $value->getStockMin(),
                'BuyingPrice' => Helper::convertNumberToABString($value->getBuyingPrice()),
                'SellingPrice' => Helper::convertNumberToABString($value->getPrice()),
                'Keywords' => $value->getKeywords(),
                'TaxRate' => Helper::convertNumberToABString($value->getTax()),
                'ProductBrand' => $value->getManufacturer(),
                'ImageLargeURL' => $value->getMainImageUrl(),
                'ImageSmallURL' => $value->getMainImageThumbnailUrl(),
                'AddCatalogs' => $this->buildAfterbuyCatalogAssignment($value->getExternalCategoryIds()),
                'Stock' => ($value->isLastStock() === true) ? 1 : 0,
                'Discontinued' => ($value->isLastStock() === true) ? 1 : 0,
                'BasepriceFactor' => $value->getBasePriceFactor()
            )
        );

        $product['Product']['ProductPictures'] = $this->buildAfterbuyImages($value->getProductPictures());

        return $product;
    }

    /**
     * @param array        $product
     * @param ValueArticle $valueArticle
     */
    public function addProductPictures(array $product, ValueArticle $valueArticle)
    {
        $mainPicture = new ProductPicture();
        $mainPicture->setNr(0);
        $mainPicture->setUrl($product['ImageLargeURL']);

        $valueArticle->addProductPicture($mainPicture);

        $hasPictures =
            array_key_exists('ProductPictures', $product)
            && array_key_exists('ProductPicture', $product['ProductPictures']);
        if ( ! $hasPictures) {
            return;
        }

        $productPictures = $product['ProductPictures']['ProductPicture'];

        if ( ! array_key_exists(0, $productPictures)) {
            $productPictures = [$productPictures];
        }

        foreach ($productPictures as $productPicture) {

            $valuePicture = new ProductPicture();
            $valuePicture->setNr($productPicture['Nr']);
            $valuePicture->setUrl($productPicture['Url']);
            $valuePicture->setAltText($productPicture['AltText']);

            $valueArticle->addProductPicture($valuePicture);

        }
    }

    /**
     * @param ValueArticle $valueArticle
     * @param $product
     */
    public function readAttributes(ValueArticle $valueArticle, $product)
    {
        $articleProperties = [];

        if(!array_key_exists('Attributes', $product) || !array_key_exists('Attribut', $product['Attributes'])) {
            return;
        }

        foreach ($product['Attributes']['Attribut'] as $key => $value) {

            if(!is_array($value)) {
                continue;
            }

            if(array_key_exists('AttributType', $value)) {
                switch ($value['AttributType']) {
                    case 0:
                        $value['AttributType'] = 'Text';
                        break;
                    case 1:
                        $value['AttributType'] = 'Textfeld';
                        break;
                    case 2:
                        $value['AttributType'] = 'Dropdown';
                        break;
                    case 3:
                        $value['AttributType'] = 'Link';
                        break;
                }

                $articleProperties[] = [
                    'name' => $value['AttributName'],
                    'value' => $value['AttributValue'],
                    'type' => $value['AttributType'],
                    'required' => ($value['AttributRequired'] == '-1') ? false : true,
                    'position' => $value['AttributPosition'],
                ];
            }
        }

        $valueArticle->setArticleProperties($articleProperties);
    }

    /**
     * @param ValueArticle $valueArticle
     * @param array $product
     * @return ValueArticle
     */
    public function setDefaultArticleValues(ValueArticle $valueArticle, array $product) {
        $valueArticle->setEan($product['EAN']);
        $valueArticle->setName($product['Name']);
        $valueArticle->setPrice(Helper::convertDeString2Float($product['SellingPrice']));
        $valueArticle->setManufacturer($product['ProductBrand']);
        $valueArticle->setStock($product['Quantity']);
        $valueArticle->setStockMin((int)$product['MinimumStock']);
        $valueArticle->setTax(Helper::convertDeString2Float($product['TaxRate']));
        $valueArticle->setDescription($product['Description']);
        $valueArticle->setShortDescription($product['ShortDescription']);
        $valueArticle->setUnitOfQuantity($product['UnitOfQuantity']);
        $valueArticle->setBasePriceFactor($product['BasepriceFactor']);
        $valueArticle->setWeight($product['Weight']);
        $valueArticle->setSupplierNumber($product['ManufacturerPartNumber']);
        $valueArticle->setDiscontinued($product['Discontinued']);
        $valueArticle->setBuyingPrice(Helper::convertDeString2Float($product['BuyingPrice']));
        $valueArticle->setKeywords($product['Keywords']);

        $valueArticle->setFree1(key_exists('FreeValue1', $product) ? $product['FreeValue1'] : '');
        $valueArticle->setFree2(key_exists('FreeValue2', $product) ? $product['FreeValue2'] : '');
        $valueArticle->setFree3(key_exists('FreeValue3', $product) ? $product['FreeValue3'] : '');
        $valueArticle->setFree4(key_exists('FreeValue4', $product) ? $product['FreeValue4'] : '');
        $valueArticle->setFree5(key_exists('FreeValue5', $product) ? $product['FreeValue5'] : '');
        $valueArticle->setFree6(key_exists('FreeValue6', $product) ? $product['FreeValue6'] : '');
        $valueArticle->setFree7(key_exists('FreeValue7', $product) ? $product['FreeValue7'] : '');
        $valueArticle->setFree8(key_exists('FreeValue8', $product) ? $product['FreeValue8'] : '');
        $valueArticle->setFree9(key_exists('FreeValue9', $product) ? $product['FreeValue9'] : '');
        $valueArticle->setFree10(key_exists('FreeValue10', $product) ? $product['FreeValue10'] : '');

        if ((int)$product['Quantity'] > (int)$product['MinimumStock'] && Helper::convertDeString2Float($product['SellingPrice'] > 0)) {
            $valueArticle->setActive(true);
        }

        return $valueArticle;
    }

    /**
     * @param array $product
     * @param string $targetEntity
     * @return ValueArticle
     */
    public function createValueArticle(array $product, string $targetEntity) {
        /** @var ValueArticle $valueArticle */
        $valueArticle = new $targetEntity();

        $valueArticle->setExternalIdentifier($product['ProductID']);
        $valueArticle->setAnr($product['Anr']);

        if((int)$this->config['ordernumberMapping'] === 1) {
            $valueArticle->setOrdernunmber($valueArticle->getAnr());
        }
        else {
            $valueArticle->setOrdernunmber($valueArticle->getExternalIdentifier());
        }

        return $valueArticle;
    }

    /**
     * @param ValueArticle $valueArticle
     * @param array $product
     * @return ValueArticle
     */
    public function addCatalogs(ValueArticle $valueArticle, array $product) {
        if (array_key_exists('Catalogs', $product) && array_key_exists('CatalogID', $product['Catalogs'])) {
            $catalogIDs = $product['Catalogs']['CatalogID'];
            if ( ! is_array($catalogIDs)) {
                $catalogIDs = [$catalogIDs];
            }

            $valueArticle->setExternalCategoryIds($catalogIDs);
        }

        return $valueArticle;
    }

    /**
     * @param ValueArticle $valueArticle
     * @param array $product
     * @return ValueArticle
     */
    public function setVariants(ValueArticle $valueArticle, array $product) {
        $variants = [];

        // variants without attribute option association
        if (!array_key_exists('Attributes', $product) && array_key_exists('BaseProducts', $product) && $product['BaseProductFlag'] !== '1'
            && array_key_exists('BaseProductID', $product['BaseProducts']['BaseProduct'])) {
            $valueArticle->setMainArticleId($product['BaseProducts']['BaseProduct']['BaseProductID']);

            $variants[] = array(
                'option' => 'Variation',
                'value'  => $product['Name'],
            );
        }

        // variants assigned via after attribute options (e.g. color, size)
        if (array_key_exists('Attributes', $product) && array_key_exists('BaseProducts', $product) && $product['BaseProductFlag'] !== '1'
            && array_key_exists('BaseProductID', $product['BaseProducts']['BaseProduct'])) {
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

        if (
            key_exists('BaseProductFlag', $product) and $product['BaseProductFlag'] !== '1'
            or !key_exists('BaseProductFlag', $product)
        ) {
            $this->readAttributes($valueArticle, $product);
        }

        if ( ! empty($variants) && $product['BaseProductFlag'] !== '1') {
            $valueArticle->setVariants($variants);
        }

        return $valueArticle;
    }
}

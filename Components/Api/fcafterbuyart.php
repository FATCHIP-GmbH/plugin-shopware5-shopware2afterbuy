<?php
/**
 * Created by PhpStorm.
 * User: Hendrik
 * Date: 04.09.2017
 * Time: 23:03
 */
namespace Shopware\FatchipShopware2Afterbuy\Components\Api;

class fcafterbuyart
{
    /**
     * Representation of possible values of an afterbuy article
     * @var array
     */
    protected $_aArticleAttributes = array(
        'BaseProductType' => '0',
        'UserProductID' => null,
        'Anr' => null,
        'EAN' => null,
        'ProductID' => null,
        'Name' => null,
        'ManufacturerPartNumber' => null,
        'ShortDescription' => null,
        'Memo' => null,
        'Description' => null,
        'Keywords' => null,
        'Quantity' => null,
        'AuctionQuantity' => null,
        'AddQuantity' => null,
        'AddAuctionQuantity' => null,
        'Stock' => null,
        'Discontinued' => null,
        'MergeStock' => null,
        'UnitOfQuantity' => null,
        'BasepriceFactor' => null,
        'MinimumStock' => null,
        'SellingPrice' => null,
        'BuyingPrice' => null,
        'DealerPrice' => null,
        'Level' => null,
        'Position' => null,
        'TitleReplace' => null,
        'ScaledQuantity' => null,
        'ScaledPrice' => null,
        'ScaledDPrice' => null,
        'TaxRate' => null,
        'Weight' => null,
        'Stocklocation_1' => null,
        'Stocklocation_2' => null,
        'Stocklocation_3' => null,
        'Stocklocation_4' => null,
        'CountryOfOrigin' => null,
        'SearchAlias' => null,
        'Froogle' => null,
        'Kelkoo' => null,
        'ShippingGroup' => null,
        'ShopShippingGroup' => null,
        'CrossCatalogID' => null,
        'FreeValue1' => null,
        'FreeValue2' => null,
        'FreeValue3' => null,
        'FreeValue4' => null,
        'FreeValue5' => null,
        'FreeValue6' => null,
        'FreeValue7' => null,
        'FreeValue8' => null,
        'FreeValue9' => null,
        'FreeValue10' => null,
        'DeliveryTime' => null,
        'ImageSmallURL' => null,
        'ImageLargeURL' => null,
        'ImageName' => null,
        'ImageSource' => null,
        'ManufacturerStandardProductIDType' => null,
        'ManufacturerStandardProductIDValue' => null,
        'ProductBrand' => null,
        'CustomsTariffNumber' => null,
        'GoogleProductCategory' => null,
        'Condition' => null,
        'Pattern' => null,
        'Material' => null,
        'ItemColor' => null,
        'ItemSize' => null,
        'CanonicalUrl' => null,
        'EnergyClass' => null,
        'EnergyClassPictureUrl' => null,
        'Gender' => null,
        'AgeGroup' => null,
        'ProductPicture_Nr_1' => null,
        'ProductPicture_Nr_2' => null,
        'ProductPicture_Nr_3' => null,
        'ProductPicture_Nr_4' => null,
        'ProductPicture_Nr_5' => null,
        'ProductPicture_Nr_6' => null,
        'ProductPicture_Nr_7' => null,
        'ProductPicture_Nr_8' => null,
        'ProductPicture_Nr_9' => null,
        'ProductPicture_Nr_10' => null,
        'ProductPicture_Nr_11' => null,
        'ProductPicture_Nr_12' => null,
        'ProductPicture_Url_1' => null,
        'ProductPicture_Url_2' => null,
        'ProductPicture_Url_3' => null,
        'ProductPicture_Url_4' => null,
        'ProductPicture_Url_5' => null,
        'ProductPicture_Url_6' => null,
        'ProductPicture_Url_7' => null,
        'ProductPicture_Url_8' => null,
        'ProductPicture_Url_9' => null,
        'ProductPicture_Url_10' => null,
        'ProductPicture_Url_11' => null,
        'ProductPicture_Url_12' => null,
        'ProductPicture_AltText_1' => null,
        'ProductPicture_AltText_2' => null,
        'ProductPicture_AltText_3' => null,
        'ProductPicture_AltText_4' => null,
        'ProductPicture_AltText_5' => null,
        'ProductPicture_AltText_6' => null,
        'ProductPicture_AltText_7' => null,
        'ProductPicture_AltText_8' => null,
        'ProductPicture_AltText_9' => null,
        'ProductPicture_AltText_10' => null,
        'ProductPicture_AltText_11' => null,
        'ProductPicture_AltText_12' => null,
    );

    /**
     * Magic setter
     *
     * @param string $sName
     * @param mixed $mValue
     * @return void
     */
    public function __set($sName, $mValue) {
        $this->_aArticleAttributes[$sName] = $mValue;
    }

    /**
     * Magic getter
     *
     * @param $sName
     * @return mixed
     */
    public function __get($sName) {
        return $this->_aArticleAttributes[$sName];
    }
    
}

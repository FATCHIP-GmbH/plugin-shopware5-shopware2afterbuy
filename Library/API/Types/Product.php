<?php

namespace Fatchip\Afterbuy\Types;

use Fatchip\Afterbuy\Types\Product\AddAttributes;
use Fatchip\Afterbuy\Types\Product\AddBaseProducts;
use Fatchip\Afterbuy\Types\Product\AddCatalogs;
use Fatchip\Afterbuy\Types\Product\AdditionalDescriptionFields;
use Fatchip\Afterbuy\Types\Product\PartsFitment;
use Fatchip\Afterbuy\Types\Product\ProductIdent;
use Fatchip\Afterbuy\Types\Product\ProductPictures;
use Fatchip\Afterbuy\Types\Product\ScaledDiscounts;
use Fatchip\Afterbuy\Types\Product\Skus;

class Product
{
    /** @var ProductIdent */
    private $ProductIdent;

    /** @var float */
    private $Anr;
    /** @var string */
    private $EAN;
    /** @var int */
    private $FooterID;
    /** @var int */
    private $HeaderID;
    /** @var string */
    private $Name;
    /** @var string */
    private $ManufacturerPartNumber;
    /** @var string */
    private $ShortDescription;
    /** @var string */
    private $Memo;
    /** @var string */
    private $Description;
    /** @var string */
    private $Keywords;
    /** @var int */
    private $Quantity;
    /** @var int */
    private $AuctionQuantity;
    /** @var bool */
    private $Stock;
    /** @var bool */
    private $Discontinued;
    /** @var bool */
    private $MergeStock;
    /** @var float */
    private $UnitOfQuantity;
    /** @var string */
    private $BasepriceFactor;
    /** @var int */
    private $MinimumStock;
    /** @var float */
    private $SellingPrice;
    /** @var float */
    private $BuyingPrice;
    /** @var float */
    private $DealerPrice;
    /** @var int */
    private $Level;
    /** @var int */
    private $Position;
    /** @var bool */
    private $TitleReplace;
    /** @var float */
    private $TaxRate;
    /** @var float */
    private $Weight;
    /** @var string */
    private $Stocklocation_1;
    /** @var string */
    private $Stocklocation_2;
    /** @var string */
    private $Stocklocation_3;
    /** @var string */
    private $Stocklocation_4;
    /** @var string */
    private $CountryOfOrigin;
    /** @var string */
    private $SearchAlias;
    /** @var bool */
    private $Froogle;
    /** @var bool */
    private $Kelkoo;
    /** @var string */
    private $ShippingGroup;
    /** @var string */
    private $ShopShippingGroup;
    /** @var int */
    private $CrossCatalogID;
    /** @var string */
    private $FreeValue1;
    /** @var string */
    private $FreeValue2;
    /** @var string */
    private $FreeValue3;
    /** @var string */
    private $FreeValue4;
    /** @var string */
    private $FreeValue5;
    /** @var string */
    private $FreeValue6;
    /** @var string */
    private $FreeValue7;
    /** @var string */
    private $FreeValue8;
    /** @var string */
    private $FreeValue9;
    /** @var string */
    private $FreeValue10;
    /** @var string */
    private $DeliveryTime;
    /** @var string */
    private $ImageSmallURL;
    /** @var string */
    private $ImageLargeURL;
    /** @var string */
    private $ImageName;
    /** @var string */
    private $ImageSource;
    /** @var string */
    private $ManufacturerStandardProductIDType;
    /** @var string */
    private $ManufacturerStandardProductIDValue;
    /** @var string */
    private $ProductBrand;
    /** @var string */
    private $CustomsTariffNumber;
    /** @var string */
    private $GoogleProductCategory;
    /** @var int */
    private $Condition;
    /** @var string */
    private $Pattern;
    /** @var string */
    private $Material;
    /** @var string */
    private $ItemColor;
    /** @var string */
    private $ItemSize;
    /** @var string */
    private $CanonicalUrl;
    /** @var int */
    private $EnergyClass;
    /** @var string */
    private $EnergyClassPictureUrl;
    /** @var int */
    private $AgeGroup;
    /** @var int */
    private $Gender;

    /** @var Skus */
    private $Skus;
    /** @var AddCatalogs */
    private $AddCatalogs;
    /** @var AddAttributes */
    private $AddAttributes;
    /** @var AddBaseProducts */
    private $AddBaseProducts;
    /** @var PartsFitment */
    private $PartsFitment;
    /** @var ProductPictures */
    private $ProductPictures;
    /** @var ScaledDiscounts */
    private $ScaledDiscounts;
    /** @var AdditionalDescriptionFields */
    private $AdditionalDescriptionFields;

    /**
     * Product constructor.
     * @param ProductIdent|int $ProductIdent
     */
    public function __construct($ProductIdent = null)
    {
        if (empty($ProductIdent)) {
            $this->ProductIdent = new ProductIdent();
        } elseif (is_integer($ProductIdent)) {
            $this->ProductIdent = new ProductIdent($ProductIdent);
        } elseif ($ProductIdent instanceof ProductIdent) {
            $this->ProductIdent = $ProductIdent;
        } else {
            throw new \InvalidArgumentException(
                "Given value for argument 'ProductIdent' is not valid"
            );
        }
    }

    /**
     * @return ProductIdent
     */
    public function getProductIdent()
    {
        return $this->ProductIdent;
    }

    /**
     * @param ProductIdent $ProductIdent
     * @return Product
     */
    public function setProductIdent($ProductIdent)
    {
        $this->ProductIdent = $ProductIdent;
        return $this;
    }

    /**
     * @return float
     */
    public function getAnr()
    {
        return $this->Anr;
    }

    /**
     * @param float $Anr
     * @return Product
     */
    public function setAnr($Anr)
    {
        $this->Anr = $Anr;
        return $this;
    }

    /**
     * @return string
     */
    public function getEAN()
    {
        return $this->EAN;
    }

    /**
     * @param string $EAN
     * @return Product
     */
    public function setEAN($EAN)
    {
        $this->EAN = $EAN;
        return $this;
    }

    /**
     * @return int
     */
    public function getFooterID()
    {
        return $this->FooterID;
    }

    /**
     * @param int $FooterID
     * @return Product
     */
    public function setFooterID($FooterID)
    {
        $this->FooterID = $FooterID;
        return $this;
    }

    /**
     * @return int
     */
    public function getHeaderID()
    {
        return $this->HeaderID;
    }

    /**
     * @param int $HeaderID
     * @return Product
     */
    public function setHeaderID($HeaderID)
    {
        $this->HeaderID = $HeaderID;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->Name;
    }

    /**
     * @param string $Name
     * @return Product
     */
    public function setName($Name)
    {
        $this->Name = $Name;
        return $this;
    }

    /**
     * @return string
     */
    public function getManufacturerPartNumber()
    {
        return $this->ManufacturerPartNumber;
    }

    /**
     * @param string $ManufacturerPartNumber
     * @return Product
     */
    public function setManufacturerPartNumber($ManufacturerPartNumber)
    {
        $this->ManufacturerPartNumber = $ManufacturerPartNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getShortDescription()
    {
        return $this->ShortDescription;
    }

    /**
     * @param string $ShortDescription
     * @return Product
     */
    public function setShortDescription($ShortDescription)
    {
        $this->ShortDescription = $ShortDescription;
        return $this;
    }

    /**
     * @return string
     */
    public function getMemo()
    {
        return $this->Memo;
    }

    /**
     * @param string $Memo
     * @return Product
     */
    public function setMemo($Memo)
    {
        $this->Memo = $Memo;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->Description;
    }

    /**
     * @param string $Description
     * @return Product
     */
    public function setDescription($Description)
    {
        $this->Description = $Description;
        return $this;
    }

    /**
     * @return string
     */
    public function getKeywords()
    {
        return $this->Keywords;
    }

    /**
     * @param string $Keywords
     * @return Product
     */
    public function setKeywords($Keywords)
    {
        $this->Keywords = $Keywords;
        return $this;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->Quantity;
    }

    /**
     * @param int $Quantity
     * @return Product
     */
    public function setQuantity($Quantity)
    {
        $this->Quantity = $Quantity;
        return $this;
    }

    /**
     * @return int
     */
    public function getAuctionQuantity()
    {
        return $this->AuctionQuantity;
    }

    /**
     * @param int $AuctionQuantity
     * @return Product
     */
    public function setAuctionQuantity($AuctionQuantity)
    {
        $this->AuctionQuantity = $AuctionQuantity;
        return $this;
    }

    /**
     * @return bool
     */
    public function isStock()
    {
        return $this->Stock;
    }

    /**
     * @param bool $Stock
     * @return Product
     */
    public function setStock($Stock)
    {
        $this->Stock = $Stock;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDiscontinued()
    {
        return $this->Discontinued;
    }

    /**
     * @param bool $Discontinued
     * @return Product
     */
    public function setDiscontinued($Discontinued)
    {
        $this->Discontinued = $Discontinued;
        return $this;
    }

    /**
     * @return bool
     */
    public function isMergeStock()
    {
        return $this->MergeStock;
    }

    /**
     * @param bool $MergeStock
     * @return Product
     */
    public function setMergeStock($MergeStock)
    {
        $this->MergeStock = $MergeStock;
        return $this;
    }

    /**
     * @return float
     */
    public function getUnitOfQuantity()
    {
        return $this->UnitOfQuantity;
    }

    /**
     * @param float $UnitOfQuantity
     * @return Product
     */
    public function setUnitOfQuantity($UnitOfQuantity)
    {
        $this->UnitOfQuantity = $UnitOfQuantity;
        return $this;
    }

    /**
     * @return string
     */
    public function getBasepriceFactor()
    {
        return $this->BasepriceFactor;
    }

    /**
     * @param string $BasepriceFactor
     * @return Product
     */
    public function setBasepriceFactor($BasepriceFactor)
    {
        $this->BasepriceFactor = $BasepriceFactor;
        return $this;
    }

    /**
     * @return int
     */
    public function getMinimumStock()
    {
        return $this->MinimumStock;
    }

    /**
     * @param int $MinimumStock
     * @return Product
     */
    public function setMinimumStock($MinimumStock)
    {
        $this->MinimumStock = $MinimumStock;
        return $this;
    }

    /**
     * @return float
     */
    public function getSellingPrice()
    {
        return $this->SellingPrice;
    }

    /**
     * @param float $SellingPrice
     * @return Product
     */
    public function setSellingPrice($SellingPrice)
    {
        $this->SellingPrice = $SellingPrice;
        return $this;
    }

    /**
     * @return float
     */
    public function getBuyingPrice()
    {
        return $this->BuyingPrice;
    }

    /**
     * @param float $BuyingPrice
     * @return Product
     */
    public function setBuyingPrice($BuyingPrice)
    {
        $this->BuyingPrice = $BuyingPrice;
        return $this;
    }

    /**
     * @return float
     */
    public function getDealerPrice()
    {
        return $this->DealerPrice;
    }

    /**
     * @param float $DealerPrice
     * @return Product
     */
    public function setDealerPrice($DealerPrice)
    {
        $this->DealerPrice = $DealerPrice;
        return $this;
    }

    /**
     * @return int
     */
    public function getLevel()
    {
        return $this->Level;
    }

    /**
     * @param int $Level
     * @return Product
     */
    public function setLevel($Level)
    {
        $this->Level = $Level;
        return $this;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->Position;
    }

    /**
     * @param int $Position
     * @return Product
     */
    public function setPosition($Position)
    {
        $this->Position = $Position;
        return $this;
    }

    /**
     * @return bool
     */
    public function isTitleReplace()
    {
        return $this->TitleReplace;
    }

    /**
     * @param bool $TitleReplace
     * @return Product
     */
    public function setTitleReplace($TitleReplace)
    {
        $this->TitleReplace = $TitleReplace;
        return $this;
    }

    /**
     * @return float
     */
    public function getTaxRate()
    {
        return $this->TaxRate;
    }

    /**
     * @param float $TaxRate
     * @return Product
     */
    public function setTaxRate($TaxRate)
    {
        $this->TaxRate = $TaxRate;
        return $this;
    }

    /**
     * @return float
     */
    public function getWeight()
    {
        return $this->Weight;
    }

    /**
     * @param float $Weight
     * @return Product
     */
    public function setWeight($Weight)
    {
        $this->Weight = $Weight;
        return $this;
    }

    /**
     * @return string
     */
    public function getStocklocation1()
    {
        return $this->Stocklocation_1;
    }

    /**
     * @param string $Stocklocation_1
     * @return Product
     */
    public function setStocklocation1($Stocklocation_1)
    {
        $this->Stocklocation_1 = $Stocklocation_1;
        return $this;
    }

    /**
     * @return string
     */
    public function getStocklocation2()
    {
        return $this->Stocklocation_2;
    }

    /**
     * @param string $Stocklocation_2
     * @return Product
     */
    public function setStocklocation2($Stocklocation_2)
    {
        $this->Stocklocation_2 = $Stocklocation_2;
        return $this;
    }

    /**
     * @return string
     */
    public function getStocklocation3()
    {
        return $this->Stocklocation_3;
    }

    /**
     * @param string $Stocklocation_3
     * @return Product
     */
    public function setStocklocation3($Stocklocation_3)
    {
        $this->Stocklocation_3 = $Stocklocation_3;
        return $this;
    }

    /**
     * @return string
     */
    public function getStocklocation4()
    {
        return $this->Stocklocation_4;
    }

    /**
     * @param string $Stocklocation_4
     * @return Product
     */
    public function setStocklocation4($Stocklocation_4)
    {
        $this->Stocklocation_4 = $Stocklocation_4;
        return $this;
    }

    /**
     * @return string
     */
    public function getCountryOfOrigin()
    {
        return $this->CountryOfOrigin;
    }

    /**
     * @param string $CountryOfOrigin
     * @return Product
     */
    public function setCountryOfOrigin($CountryOfOrigin)
    {
        $this->CountryOfOrigin = $CountryOfOrigin;
        return $this;
    }

    /**
     * @return string
     */
    public function getSearchAlias()
    {
        return $this->SearchAlias;
    }

    /**
     * @param string $SearchAlias
     * @return Product
     */
    public function setSearchAlias($SearchAlias)
    {
        $this->SearchAlias = $SearchAlias;
        return $this;
    }

    /**
     * @return bool
     */
    public function isFroogle()
    {
        return $this->Froogle;
    }

    /**
     * @param bool $Froogle
     * @return Product
     */
    public function setFroogle($Froogle)
    {
        $this->Froogle = $Froogle;
        return $this;
    }

    /**
     * @return bool
     */
    public function isKelkoo()
    {
        return $this->Kelkoo;
    }

    /**
     * @param bool $Kelkoo
     * @return Product
     */
    public function setKelkoo($Kelkoo)
    {
        $this->Kelkoo = $Kelkoo;
        return $this;
    }

    /**
     * @return string
     */
    public function getShippingGroup()
    {
        return $this->ShippingGroup;
    }

    /**
     * @param string $ShippingGroup
     * @return Product
     */
    public function setShippingGroup($ShippingGroup)
    {
        $this->ShippingGroup = $ShippingGroup;
        return $this;
    }

    /**
     * @return string
     */
    public function getShopShippingGroup()
    {
        return $this->ShopShippingGroup;
    }

    /**
     * @param string $ShopShippingGroup
     * @return Product
     */
    public function setShopShippingGroup($ShopShippingGroup)
    {
        $this->ShopShippingGroup = $ShopShippingGroup;
        return $this;
    }

    /**
     * @return int
     */
    public function getCrossCatalogID()
    {
        return $this->CrossCatalogID;
    }

    /**
     * @param int $CrossCatalogID
     * @return Product
     */
    public function setCrossCatalogID($CrossCatalogID)
    {
        $this->CrossCatalogID = $CrossCatalogID;
        return $this;
    }

    /**
     * @return string
     */
    public function getFreeValue1()
    {
        return $this->FreeValue1;
    }

    /**
     * @param string $FreeValue1
     * @return Product
     */
    public function setFreeValue1($FreeValue1)
    {
        $this->FreeValue1 = $FreeValue1;
        return $this;
    }

    /**
     * @return string
     */
    public function getFreeValue2()
    {
        return $this->FreeValue2;
    }

    /**
     * @param string $FreeValue2
     * @return Product
     */
    public function setFreeValue2($FreeValue2)
    {
        $this->FreeValue2 = $FreeValue2;
        return $this;
    }

    /**
     * @return string
     */
    public function getFreeValue3()
    {
        return $this->FreeValue3;
    }

    /**
     * @param string $FreeValue3
     * @return Product
     */
    public function setFreeValue3($FreeValue3)
    {
        $this->FreeValue3 = $FreeValue3;
        return $this;
    }

    /**
     * @return string
     */
    public function getFreeValue4()
    {
        return $this->FreeValue4;
    }

    /**
     * @param string $FreeValue4
     * @return Product
     */
    public function setFreeValue4($FreeValue4)
    {
        $this->FreeValue4 = $FreeValue4;
        return $this;
    }

    /**
     * @return string
     */
    public function getFreeValue5()
    {
        return $this->FreeValue5;
    }

    /**
     * @param string $FreeValue5
     * @return Product
     */
    public function setFreeValue5($FreeValue5)
    {
        $this->FreeValue5 = $FreeValue5;
        return $this;
    }

    /**
     * @return string
     */
    public function getFreeValue6()
    {
        return $this->FreeValue6;
    }

    /**
     * @param string $FreeValue6
     * @return Product
     */
    public function setFreeValue6($FreeValue6)
    {
        $this->FreeValue6 = $FreeValue6;
        return $this;
    }

    /**
     * @return string
     */
    public function getFreeValue7()
    {
        return $this->FreeValue7;
    }

    /**
     * @param string $FreeValue7
     * @return Product
     */
    public function setFreeValue7($FreeValue7)
    {
        $this->FreeValue7 = $FreeValue7;
        return $this;
    }

    /**
     * @return string
     */
    public function getFreeValue8()
    {
        return $this->FreeValue8;
    }

    /**
     * @param string $FreeValue8
     * @return Product
     */
    public function setFreeValue8($FreeValue8)
    {
        $this->FreeValue8 = $FreeValue8;
        return $this;
    }

    /**
     * @return string
     */
    public function getFreeValue9()
    {
        return $this->FreeValue9;
    }

    /**
     * @param string $FreeValue9
     * @return Product
     */
    public function setFreeValue9($FreeValue9)
    {
        $this->FreeValue9 = $FreeValue9;
        return $this;
    }

    /**
     * @return string
     */
    public function getFreeValue10()
    {
        return $this->FreeValue10;
    }

    /**
     * @param string $FreeValue10
     * @return Product
     */
    public function setFreeValue10($FreeValue10)
    {
        $this->FreeValue10 = $FreeValue10;
        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryTime()
    {
        return $this->DeliveryTime;
    }

    /**
     * @param string $DeliveryTime
     * @return Product
     */
    public function setDeliveryTime($DeliveryTime)
    {
        $this->DeliveryTime = $DeliveryTime;
        return $this;
    }

    /**
     * @return string
     */
    public function getImageSmallURL()
    {
        return $this->ImageSmallURL;
    }

    /**
     * @param string $ImageSmallURL
     * @return Product
     */
    public function setImageSmallURL($ImageSmallURL)
    {
        $this->ImageSmallURL = $ImageSmallURL;
        return $this;
    }

    /**
     * @return string
     */
    public function getImageLargeURL()
    {
        return $this->ImageLargeURL;
    }

    /**
     * @param string $ImageLargeURL
     * @return Product
     */
    public function setImageLargeURL($ImageLargeURL)
    {
        $this->ImageLargeURL = $ImageLargeURL;
        return $this;
    }

    /**
     * @return string
     */
    public function getImageName()
    {
        return $this->ImageName;
    }

    /**
     * @param string $ImageName
     * @return Product
     */
    public function setImageName($ImageName)
    {
        $this->ImageName = $ImageName;
        return $this;
    }

    /**
     * @return string
     */
    public function getImageSource()
    {
        return $this->ImageSource;
    }

    /**
     * @param string $ImageSource
     * @return Product
     */
    public function setImageSource($ImageSource)
    {
        $this->ImageSource = $ImageSource;
        return $this;
    }

    /**
     * @return string
     */
    public function getManufacturerStandardProductIDType()
    {
        return $this->ManufacturerStandardProductIDType;
    }

    /**
     * @param string $ManufacturerStandardProductIDType
     * @return Product
     */
    public function setManufacturerStandardProductIDType($ManufacturerStandardProductIDType)
    {
        $this->ManufacturerStandardProductIDType = $ManufacturerStandardProductIDType;
        return $this;
    }

    /**
     * @return string
     */
    public function getManufacturerStandardProductIDValue()
    {
        return $this->ManufacturerStandardProductIDValue;
    }

    /**
     * @param string $ManufacturerStandardProductIDValue
     * @return Product
     */
    public function setManufacturerStandardProductIDValue($ManufacturerStandardProductIDValue)
    {
        $this->ManufacturerStandardProductIDValue = $ManufacturerStandardProductIDValue;
        return $this;
    }

    /**
     * @return string
     */
    public function getProductBrand()
    {
        return $this->ProductBrand;
    }

    /**
     * @param string $ProductBrand
     * @return Product
     */
    public function setProductBrand($ProductBrand)
    {
        $this->ProductBrand = $ProductBrand;
        return $this;
    }

    /**
     * @return string
     */
    public function getCustomsTariffNumber()
    {
        return $this->CustomsTariffNumber;
    }

    /**
     * @param string $CustomsTariffNumber
     * @return Product
     */
    public function setCustomsTariffNumber($CustomsTariffNumber)
    {
        $this->CustomsTariffNumber = $CustomsTariffNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getGoogleProductCategory()
    {
        return $this->GoogleProductCategory;
    }

    /**
     * @param string $GoogleProductCategory
     * @return Product
     */
    public function setGoogleProductCategory($GoogleProductCategory)
    {
        $this->GoogleProductCategory = $GoogleProductCategory;
        return $this;
    }

    /**
     * @return int
     */
    public function getCondition()
    {
        return $this->Condition;
    }

    /**
     * @param int $Condition
     * @return Product
     */
    public function setCondition($Condition)
    {
        $this->Condition = $Condition;
        return $this;
    }

    /**
     * @return string
     */
    public function getPattern()
    {
        return $this->Pattern;
    }

    /**
     * @param string $Pattern
     * @return Product
     */
    public function setPattern($Pattern)
    {
        $this->Pattern = $Pattern;
        return $this;
    }

    /**
     * @return string
     */
    public function getMaterial()
    {
        return $this->Material;
    }

    /**
     * @param string $Material
     * @return Product
     */
    public function setMaterial($Material)
    {
        $this->Material = $Material;
        return $this;
    }

    /**
     * @return string
     */
    public function getItemColor()
    {
        return $this->ItemColor;
    }

    /**
     * @param string $ItemColor
     * @return Product
     */
    public function setItemColor($ItemColor)
    {
        $this->ItemColor = $ItemColor;
        return $this;
    }

    /**
     * @return string
     */
    public function getItemSize()
    {
        return $this->ItemSize;
    }

    /**
     * @param string $ItemSize
     * @return Product
     */
    public function setItemSize($ItemSize)
    {
        $this->ItemSize = $ItemSize;
        return $this;
    }

    /**
     * @return string
     */
    public function getCanonicalUrl()
    {
        return $this->CanonicalUrl;
    }

    /**
     * @param string $CanonicalUrl
     * @return Product
     */
    public function setCanonicalUrl($CanonicalUrl)
    {
        $this->CanonicalUrl = $CanonicalUrl;
        return $this;
    }

    /**
     * @return int
     */
    public function getEnergyClass()
    {
        return $this->EnergyClass;
    }

    /**
     * @param int $EnergyClass
     * @return Product
     */
    public function setEnergyClass($EnergyClass)
    {
        $this->EnergyClass = $EnergyClass;
        return $this;
    }

    /**
     * @return string
     */
    public function getEnergyClassPictureUrl()
    {
        return $this->EnergyClassPictureUrl;
    }

    /**
     * @param string $EnergyClassPictureUrl
     * @return Product
     */
    public function setEnergyClassPictureUrl($EnergyClassPictureUrl)
    {
        $this->EnergyClassPictureUrl = $EnergyClassPictureUrl;
        return $this;
    }

    /**
     * @return int
     */
    public function getAgeGroup()
    {
        return $this->AgeGroup;
    }

    /**
     * @param int $AgeGroup
     * @return Product
     */
    public function setAgeGroup($AgeGroup)
    {
        $this->AgeGroup = $AgeGroup;
        return $this;
    }

    /**
     * @return int
     */
    public function getGender()
    {
        return $this->Gender;
    }

    /**
     * @param int $Gender
     * @return Product
     */
    public function setGender($Gender)
    {
        $this->Gender = $Gender;
        return $this;
    }

    /**
     * @return Skus
     */
    public function getSkus()
    {
        return $this->Skus;
    }

    /**
     * @param Skus $Skus
     * @return Product
     */
    public function setSkus($Skus)
    {
        $this->Skus = $Skus;
        return $this;
    }

    /**
     * @return AddCatalogs
     */
    public function getAddCatalogs()
    {
        return $this->AddCatalogs;
    }

    /**
     * @param AddCatalogs $AddCatalogs
     * @return Product
     */
    public function setAddCatalogs($AddCatalogs)
    {
        $this->AddCatalogs = $AddCatalogs;
        return $this;
    }

    /**
     * @return AddAttributes
     */
    public function getAddAttributes()
    {
        return $this->AddAttributes;
    }

    /**
     * @param AddAttributes $AddAttributes
     * @return Product
     */
    public function setAddAttributes($AddAttributes)
    {
        $this->AddAttributes = $AddAttributes;
        return $this;
    }

    /**
     * @return AddBaseProducts
     */
    public function getAddBaseProducts()
    {
        return $this->AddBaseProducts;
    }

    /**
     * @param AddBaseProducts $AddBaseProducts
     * @return Product
     */
    public function setAddBaseProducts($AddBaseProducts)
    {
        $this->AddBaseProducts = $AddBaseProducts;
        return $this;
    }

    /**
     * @return PartsFitment
     */
    public function getPartsFitment()
    {
        return $this->PartsFitment;
    }

    /**
     * @param PartsFitment $PartsFitment
     * @return Product
     */
    public function setPartsFitment($PartsFitment)
    {
        $this->PartsFitment = $PartsFitment;
        return $this;
    }

    /**
     * @return ProductPictures
     */
    public function getProductPictures()
    {
        return $this->ProductPictures;
    }

    /**
     * @param ProductPictures $ProductPictures
     * @return Product
     */
    public function setProductPictures($ProductPictures)
    {
        $this->ProductPictures = $ProductPictures;
        return $this;
    }

    /**
     * @return ScaledDiscounts
     */
    public function getScaledDiscounts()
    {
        return $this->ScaledDiscounts;
    }

    /**
     * @param ScaledDiscounts $ScaledDiscounts
     * @return Product
     */
    public function setScaledDiscounts($ScaledDiscounts)
    {
        $this->ScaledDiscounts = $ScaledDiscounts;
        return $this;
    }

    /**
     * @return AdditionalDescriptionFields
     */
    public function getAdditionalDescriptionFields()
    {
        return $this->AdditionalDescriptionFields;
    }

    /**
     * @param AdditionalDescriptionFields $AdditionalDescriptionFields
     * @return Product
     */
    public function setAdditionalDescriptionFields($AdditionalDescriptionFields)
    {
        $this->AdditionalDescriptionFields = $AdditionalDescriptionFields;
        return $this;
    }
}

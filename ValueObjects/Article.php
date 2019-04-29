<?php

namespace viaebShopwareAfterbuy\ValueObjects;

use Doctrine\Common\Collections\ArrayCollection;

class Article extends AbstractValueObject
{

    /**
     * we cannot define external identifier types, we have to handle those as strings
     *
     * @var string $externalIdentifier
     */
    public $externalIdentifier;

    /**
     * integer works with category ids, articles use strings (ordernumber)
     *
     * @var string $internalIdentifier
     */
    public $internalIdentifier;

    /**
     * @var int
     */
    public $stock;

    /**
     * @var string
     */
    public $name;

    /**
     * @var float
     */
    public $price;

    /**
     * @var string
     */
    public $manufacturer;

    /**
     * @var string
     */
    public $ean;

    public $stockMin;

    public $pseudoPrice;

    public $tax;

    public $variants = [];

    public $mainArticleId;

    public $description;

    public $supplierNumber;

    public $shortDescription;

    public $variantId;

    public $externalCategoryIds = [];

    /** @var bool */
    public $lastStock = false;

    /** @var string */
    public $unitOfQuantity;

    /** @var string */
    public $basePriceFactor;

    /** @var string */
    public $weight;

    /** @var string */
    private $discontinued;

    /** @var string */
    public $anr;

    /** @var array */
    private $articleProperties = [];

    /** @var string $ordernunmber */
    private $ordernunmber;

    /**
     * @return string
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param string $weight
     */
    public function setWeight(string $weight)
    {
        $this->weight = $weight;
    }

    /**
     * @return string
     */
    public function getBasePriceFactor()
    {
        return $this->basePriceFactor;
    }

    /**
     * @param string $basePriceFactor
     */
    public function setBasePriceFactor(string $basePriceFactor)
    {
        $this->basePriceFactor = $basePriceFactor;
    }

    /**
     * @return string
     */
    public function getUnitOfQuantity()
    {
        return $this->unitOfQuantity;
    }

    /**
     * @param string $unitOfQuantity
     */
    public function setUnitOfQuantity(string $unitOfQuantity)
    {
        $this->unitOfQuantity = $unitOfQuantity;
    }

    /**
     * @var string
     */
    public $mainImageUrl;

    /**
     * @var string
     */
    public $mainImageThumbnailUrl;

    public $free1;
    public $free2;
    public $free3;
    public $free4;
    public $free5;
    public $free6;
    public $free7;
    public $free8;
    public $free9;
    public $free10;


    /**
     * @return array
     */
    public function getExternalCategoryIds()
    {
        return $this->externalCategoryIds;
    }

    /**
     * @param array $externalCategoryIds
     */
    public function setExternalCategoryIds(array $externalCategoryIds)
    {
        $this->externalCategoryIds = $externalCategoryIds;
    }


    /**
     * @return string
     */
    public function getMainImageThumbnailUrl()
    {
        return $this->mainImageThumbnailUrl;
    }

    /**
     * @param string $mainImageThumbnailUrl
     */
    public function setMainImageThumbnailUrl(string $mainImageThumbnailUrl)
    {
        $this->mainImageThumbnailUrl = $mainImageThumbnailUrl;
    }

    /**
     * @return mixed
     */
    public function getVariantId()
    {
        return $this->variantId;
    }

    /**
     * @return string
     */
    public function getMainImageUrl()
    {
        return $this->mainImageUrl;
    }

    /**
     * @param string $mainImageUrl
     */
    public function setMainImageUrl(string $mainImageUrl)
    {
        $this->mainImageUrl = $mainImageUrl;
    }

    /**
     * @param mixed $variantId
     */
    public function setVariantId($variantId)
    {
        $this->variantId = $variantId;
    }

    /**
     * @var bool
     */
    public $active = false;

    /** @var ProductPicture[] */
    private $productPictures = [];

    protected $variantArticles;

    /**
     * @return mixed
     */
    public function getShortDescription()
    {
        return $this->shortDescription;
    }

    /**
     * @param mixed $shortDescription
     */
    public function setShortDescription($shortDescription)
    {
        $this->shortDescription = $shortDescription;
    }

    /**
     * @return mixed
     */
    public function getSupplierNumber()
    {
        return $this->supplierNumber;
    }

    /**
     * @param mixed $supplierNumber
     */
    public function setSupplierNumber($supplierNumber)
    {
        $this->supplierNumber = $supplierNumber;
    }


    public function __construct()
    {
        $this->variantArticles = new ArrayCollection();
    }

    /**
     * @return ArrayCollection
     */
    public function getVariantArticles()
    {
        return $this->variantArticles;
    }

    /**
     * @param mixed $variantArticles
     */
    public function setVariantArticles($variantArticles)
    {
        $this->variantArticles = $variantArticles;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active)
    {
        $this->active = $active;
    }


    /**
     * @return mixed
     */
    public function getMainArticleId()
    {
        return $this->mainArticleId;
    }

    /**
     * @param mixed $mainArticleId
     */
    public function setMainArticleId($mainArticleId)
    {
        $this->mainArticleId = $mainArticleId;
    }

    /**
     * @return array
     */
    public function getVariants()
    {
        return $this->variants;
    }

    /**
     * @param array $variants
     */
    public function setVariants(array $variants)
    {
        $this->variants = $variants;
    }

    /**
     * @return mixed
     */
    public function getPseudoPrice()
    {
        return $this->pseudoPrice;
    }

    /**
     * @param mixed $pseudoPrice
     */
    public function setPseudoPrice($pseudoPrice)
    {
        $this->pseudoPrice = $pseudoPrice;
    }

    /**
     * @return mixed
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * @param mixed $tax
     */
    public function setTax($tax)
    {
        $this->tax = $tax;
    }

    /**
     * @return mixed
     */
    public function getStockMin()
    {
        return $this->stockMin;
    }

    /**
     * @param mixed $stockMin
     */
    public function setStockMin($stockMin)
    {
        $this->stockMin = $stockMin;
    }


    /**
     * @return string
     */
    public function getExternalIdentifier()
    {
        return $this->externalIdentifier;
    }

    /**
     * @param string $externalIdentifier
     */
    public function setExternalIdentifier($externalIdentifier)
    {
        $this->externalIdentifier = $externalIdentifier;
    }

    /**
     * @return string
     */
    public function getInternalIdentifier()
    {
        return $this->internalIdentifier;
    }

    /**
     * @param string $internalIdentifier
     */
    public function setInternalIdentifier(string $internalIdentifier)
    {
        $this->internalIdentifier = $internalIdentifier;
    }

    /**
     * @return int
     */
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * @param int $stock
     */
    public function setStock(int $stock)
    {
        $this->stock = $stock;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice(float $price)
    {
        $this->price = $price;
    }

    /**
     * @return string
     */
    public function getManufacturer()
    {
        return $this->manufacturer;
    }

    /**
     * @param string $manufacturer
     */
    public function setManufacturer(string $manufacturer)
    {
        $this->manufacturer = $manufacturer;
    }

    /**
     * @return string
     */
    public function getEan()
    {
        return $this->ean;
    }

    /**
     * @param string $ean
     */
    public function setEan(string $ean)
    {
        $this->ean = $ean;
    }

    /**
     * @return ProductPicture[]
     */
    public function getProductPictures()
    {
        return $this->productPictures;
    }

    /**
     * @param ProductPicture $productPicture
     */
    public function addProductPicture(ProductPicture $productPicture)
    {
        $this->productPictures[] = $productPicture;
    }

    public function isMainProduct()
    {
        return $this->mainArticleId === null;
    }

    /**
     * @return string
     */
    public function getDiscontinued()
    {
        return $this->discontinued;
    }

    /**
     * @param string $discontinued
     */
    public function setDiscontinued(string $discontinued)
    {
        $this->discontinued = $discontinued;
    }

    /**
     * @return string
     */
    public function getAnr()
    {
        return $this->anr;
    }

    /**
     * @param string $anr
     */
    public function setAnr(string $anr)
    {
        $this->anr = $anr;
    }

    /**
     * @return array
     */
    public function getArticleProperties()
    {
        return $this->articleProperties;
    }

    /**
     * @param array $articleProperties
     */
    public function setArticleProperties(array $articleProperties)
    {
        $this->articleProperties = $articleProperties;
    }



    /**
     * @return mixed
     */
    public function getFree1()
    {
        return $this->free1;
    }

    /**
     * @param mixed $free1
     */
    public function setFree1($free1)
    {
        $this->free1 = $free1;
    }

    /**
     * @return mixed
     */
    public function getFree2()
    {
        return $this->free2;
    }

    /**
     * @param mixed $free2
     */
    public function setFree2($free2)
    {
        $this->free2 = $free2;
    }

    /**
     * @return mixed
     */
    public function getFree3()
    {
        return $this->free3;
    }

    /**
     * @param mixed $free3
     */
    public function setFree3($free3)
    {
        $this->free3 = $free3;
    }

    /**
     * @return mixed
     */
    public function getFree4()
    {
        return $this->free4;
    }

    /**
     * @param mixed $free4
     */
    public function setFree4($free4)
    {
        $this->free4 = $free4;
    }

    /**
     * @return mixed
     */
    public function getFree5()
    {
        return $this->free5;
    }

    /**
     * @param mixed $free5
     */
    public function setFree5($free5)
    {
        $this->free5 = $free5;
    }

    /**
     * @return mixed
     */
    public function getFree6()
    {
        return $this->free6;
    }

    /**
     * @param mixed $free6
     */
    public function setFree6($free6)
    {
        $this->free6 = $free6;
    }

    /**
     * @return mixed
     */
    public function getFree7()
    {
        return $this->free7;
    }

    /**
     * @param mixed $free7
     */
    public function setFree7($free7)
    {
        $this->free7 = $free7;
    }

    /**
     * @return mixed
     */
    public function getFree8()
    {
        return $this->free8;
    }

    /**
     * @param mixed $free8
     */
    public function setFree8($free8)
    {
        $this->free8 = $free8;
    }

    /**
     * @return mixed
     */
    public function getFree9()
    {
        return $this->free9;
    }

    /**
     * @param mixed $free9
     */
    public function setFree9($free9)
    {
        $this->free9 = $free9;
    }

    /**
     * @return mixed
     */
    public function getFree10()
    {
        return $this->free10;
    }

    /**
     * @param mixed $free10
     */
    public function setFree10($free10)
    {
        $this->free10 = $free10;
    }

    /**
     * @return string
     */
    public function getOrdernunmber(): string
    {
        return $this->ordernunmber;
    }

    /**
     * @param string $ordernunmber
     */
    public function setOrdernunmber(string $ordernunmber)
    {
        $this->ordernunmber = $ordernunmber;
    }

    /**
     * @return bool
     */
    public function isLastStock()
    {
        return $this->lastStock;
    }

    /**
     * @param bool $lastStock
     */
    public function setLastStock(bool $lastStock)
    {
        $this->lastStock = $lastStock;
    }
}
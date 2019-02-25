<?php

namespace abaccAfterbuy\ValueObjects;

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

    /**
     * @var string
     */
    public $mainImageUrl;

    /**
     * @var string
     */
    public $mainImageThumbnailUrl;

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
}
<?php

namespace Fatchip\Afterbuy\Types\Product;

class ProductPictures
{
    /** @var ProductPicture[] */
    private $ProductPicture;

    /**
     * ProductPictures constructor.
     * @param ProductPicture[] $pictures
     */
    public function __construct($pictures = null)
    {
        $this->ProductPicture = $pictures;
    }

    /**
     * @return ProductPicture[]
     */
    public function getProductPicture()
    {
        return $this->ProductPicture;
    }

    /**
     * @param ProductPicture[] $ProductPicture
     * @return ProductPictures
     */
    public function setProductPicture($ProductPicture)
    {
        $this->ProductPicture = $ProductPicture;
        return $this;
    }
}

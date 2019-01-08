<?php

namespace Fatchip\Afterbuy\Types\Product;

class ProductPicture
{
    const PICTURE_THUMB = 1;
    const PICTURE_ZOOM = 2;
    const PICTURE_LIST = 3;

    /** @var int */
    private $Nr;
    /** @var string */
    private $Url;
    /** @var string */
    private $AltText;
    /** @var array */
    private $Childs;
    /** @var int */
    private $Typ;

    /**
     * @return int
     */
    public function getNr()
    {
        return $this->Nr;
    }

    /**
     * Number may only be set for parent pictures
     *
     * @param int $Nr
     * @return ProductPicture
     */
    public function setNr($Nr)
    {
        $this->Nr = $Nr;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->Url;
    }

    /**
     * @param string $Url
     * @return ProductPicture
     */
    public function setUrl($Url)
    {
        $this->Url = $Url;
        return $this;
    }

    /**
     * @return string
     */
    public function getAltText()
    {
        return $this->AltText;
    }

    /**
     * @param string $AltText
     * @return ProductPicture
     */
    public function setAltText($AltText)
    {
        $this->AltText = $AltText;
        return $this;
    }

    /**
     * @return array
     */
    public function getChilds()
    {
        return $this->Childs;
    }

    /**
     * @param array $Childs
     * @return ProductPicture
     */
    public function setChilds($Childs)
    {
        $this->Childs = $Childs;
        return $this;
    }

    /**
     * @return int
     */
    public function getTyp()
    {
        return $this->Typ;
    }

    /**
     * Type may only be set for child pictures
     *
     * @param int $Typ
     * @return ProductPicture
     */
    public function setTyp($Typ)
    {
        $this->Typ = $Typ;
        return $this;
    }
}

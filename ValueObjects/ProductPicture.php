<?php

namespace FatchipAfterbuy\ValueObjects;

class ProductPicture
{
    public const PICTURE_THUMB = 1;
    public const PICTURE_ZOOM = 2;
    public const PICTURE_LIST = 3;

    /** @var string Afterbuy internal number 1 - 6 */
    private $nr;

    /** @var string Thumb = 1, Zoom = 2, List = 3 */
    private $typ;

    /** @var string */
    private $url;

    /** @var string */
    private $altText = '';

    /** @var ProductPicture[] */
    private $children;

    /** @var ProductPicture */
    private $parent;

    /**
     * Afterbuy internal number 1 - 6
     *
     * @return string
     */
    public function getNr(): string
    {
        return $this->nr;
    }

    /**
     * Afterbuy internal number 1 - 6
     *
     * @param string $nr
     */
    public function setNr(string $nr): void
    {
        $this->nr = $nr;
    }

    /**
     * Thumb = 1, Zoom = 2, List = 3
     *
     * @return string
     */
    public function getTyp(): string
    {
        return $this->typ;
    }

    /**
     * Thumb = 1, Zoom = 2, List = 3
     *
     * @param string $typ
     */
    public function setTyp(string $typ): void
    {
        $this->typ = $typ;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getAltText(): string
    {
        return $this->altText;
    }

    /**
     * @param string $altText
     */
    public function setAltText(?string $altText): void
    {
        if ($altText === null) {
            $altText = '';
        }
        $this->altText = $altText;
    }

    /**
     * @return ProductPicture[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @param ProductPicture $child
     */
    public function addChild($child): void
    {
        $this->children[] = $child;
    }

    /**
     * Parent has no parent: null
     *
     * @return ProductPicture
     */
    public function getParent(): ProductPicture
    {
        return $this->parent;
    }

    /**
     * Parent has no parent: null
     *
     * @param ProductPicture $parent
     */
    public function setParent(ProductPicture $parent): void
    {
        $this->parent = $parent;
    }
}

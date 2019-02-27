<?php

namespace viaebShopwareAfterbuy\ValueObjects;

class ProductPicture
{
    /** @var string Afterbuy internal position 1 - 6 */
    private $nr;

    /** @var string */
    private $url;

    /** @var string */
    private $altText = '';

    /**
     * Afterbuy internal position 1 - 6
     *
     * @return string
     */
    public function getNr()
    {
        return $this->nr;
    }

    /**
     * Afterbuy internal position 1 - 6
     *
     * @param string $nr
     */
    public function setNr(string $nr)
    {
        $this->nr = $nr;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getAltText()
    {
        return $this->altText;
    }

    /**
     * @param string $altText
     */
    public function setAltText($altText)
    {
        $altText = (string) $altText;
        if ($altText === null) {
            $altText = '';
        }
        $this->altText = $altText;
    }
}

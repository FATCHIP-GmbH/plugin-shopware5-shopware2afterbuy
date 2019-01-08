<?php

namespace Fatchip\Afterbuy\Types\Product;

class PartsProperties
{
    /** @var PartsProperty[] */
    private $PartsProperty;

    /**
     * @return PartsProperty[]
     */
    public function getPartsProperty()
    {
        return $this->PartsProperty;
    }

    /**
     * @param PartsProperty[] $PartsProperty
     * @return PartsProperties
     */
    public function setPartsProperty($PartsProperty)
    {
        $this->PartsProperty = $PartsProperty;
        return $this;
    }
}

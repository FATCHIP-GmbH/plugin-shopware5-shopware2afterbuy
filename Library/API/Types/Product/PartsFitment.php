<?php

namespace Fatchip\Afterbuy\Types\Product;

use Fatchip\Afterbuy\Types\Product\PartsProperties;

class PartsFitment
{
    /** @var PartsProperties */
    private $PartsProperties;

    /**
     * @return PartsProperties
     */
    public function getPartsProperties()
    {
        return $this->PartsProperties;
    }

    /**
     * @param PartsProperties $PartsProperties
     * @return PartsFitment
     */
    public function setPartsProperties($PartsProperties)
    {
        $this->PartsProperties = $PartsProperties;
        return $this;
    }
}

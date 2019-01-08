<?php

namespace Fatchip\Afterbuy\Types\Product;

class PartsProperty
{
    /** @var string */
    private $PropertyName;
    /** @var string */
    private $PropertyValue;

    /**
     * @return string
     */
    public function getPropertyName()
    {
        return $this->PropertyName;
    }

    /**
     * @param string $PropertyName
     * @return PartsProperty
     */
    public function setPropertyName($PropertyName)
    {
        $this->PropertyName = $PropertyName;
        return $this;
    }

    /**
     * @return string
     */
    public function getPropertyValue()
    {
        return $this->PropertyValue;
    }

    /**
     * @param string $PropertyValue
     * @return PartsProperty
     */
    public function setPropertyValue($PropertyValue)
    {
        $this->PropertyValue = $PropertyValue;
        return $this;
    }
}

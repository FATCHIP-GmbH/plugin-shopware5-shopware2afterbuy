<?php

namespace Fatchip\Afterbuy\Types\Product;

class AddAttribut
{
    /** @var string */
    private $AttributName;
    /** @var string */
    private $AttributValue;
    /** @var int */
    private $AttributTyp;
    /** @var int */
    private $AttributPosition;
    /** @var bool */
    private $AttributRequired;

    /**
     * @return string
     */
    public function getAttributName()
    {
        return $this->AttributName;
    }

    /**
     * @param string $AttributName
     * @return AddAttribut
     */
    public function setAttributName($AttributName)
    {
        $this->AttributName = $AttributName;
        return $this;
    }

    /**
     * @return string
     */
    public function getAttributValue()
    {
        return $this->AttributValue;
    }

    /**
     * @param string $AttributValue
     * @return AddAttribut
     */
    public function setAttributValue($AttributValue)
    {
        $this->AttributValue = $AttributValue;
        return $this;
    }

    /**
     * @return int
     */
    public function getAttributTyp()
    {
        return $this->AttributTyp;
    }

    /**
     * @param int $AttributTyp
     * @return AddAttribut
     */
    public function setAttributTyp($AttributTyp)
    {
        $this->AttributTyp = $AttributTyp;
        return $this;
    }

    /**
     * @return int
     */
    public function getAttributPosition()
    {
        return $this->AttributPosition;
    }

    /**
     * @param int $AttributPosition
     * @return AddAttribut
     */
    public function setAttributPosition($AttributPosition)
    {
        $this->AttributPosition = $AttributPosition;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAttributRequired()
    {
        return $this->AttributRequired;
    }

    /**
     * @param bool $AttributRequired
     * @return AddAttribut
     */
    public function setAttributRequired($AttributRequired)
    {
        $this->AttributRequired = $AttributRequired;
        return $this;
    }
}

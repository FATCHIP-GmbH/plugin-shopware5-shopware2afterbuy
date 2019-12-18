<?php
/** @noinspection SpellCheckingInspection */

namespace viaebShopwareAfterbuy\ValueObjects;

class Stock extends AbstractValueObject
{
    /**
     * @var string $externalIdentifier
     */
    public $identifyer;

    /**
     * @var int
     */
    public $stock;

    public function __construct(string $identifyer, int $stock)
    {
        $this->stock = $stock;
        $this->identifyer = $identifyer;
    }

    /**
     * @return string
     */
    public function getIdentifyer(): string
    {
        return $this->identifyer;
    }

    /**
     * @return int
     */
    public function getStock()
    {
        return $this->stock;
    }
}

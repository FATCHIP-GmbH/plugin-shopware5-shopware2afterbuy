<?php

namespace FatchipAfterbuy\ValueObjects;

class Category extends AbstractValueObject {
    public $name;

    /**
     * @var
     */
    public $externalIdentifier;

    /**
     * @var
     */
    public $internalIdentifier;


    /**
     * Category constructor.
     *
     * Type of identifier received by Apis can vary. We should handle it by default as a string, but have to take
     * care in process handlers to cast correctly!
     *
     * @param string $name
     * @param string $externalIdentifier
     * @param string $internalIdentifier
     */
    public function __construct(string $name, string $externalIdentifier, string $internalIdentifier) {

    }
}
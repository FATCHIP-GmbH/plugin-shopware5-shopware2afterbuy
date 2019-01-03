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

    public $parentIdentifier;

    public $description;

    public $position;

    public $active;

    public $image;

    public function __construct() {

    }
}
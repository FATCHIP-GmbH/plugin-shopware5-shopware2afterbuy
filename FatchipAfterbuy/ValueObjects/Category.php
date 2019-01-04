<?php

namespace FatchipAfterbuy\ValueObjects;

class Category extends AbstractValueObject {
    /**
     * @var string $name
     */
    public $name;

    /**
     * we cannot define external identifier types, we have to handle those as strings
     *
     * @var string $externalIdentifier
     */
    public $externalIdentifier;

    /**
     * integer works with category ids, articles use strings (ordernumber)
     *
     * @var int $internalIdentifier
     */
    public $internalIdentifier;

    /**
     * in that case we do refer the external id
     *
     * @var string $parentIdentifier
     */
    public $parentIdentifier;

    /**
     * metadescription
     *
     * @var string $description
     */
    public $description;

    /**
     * @var int $position
     */
    public $position;

    /**
     * @var bool $active
     */
    public $active;

    /**
     * @var string $image
     */
    public $image;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getExternalIdentifier()
    {
        return $this->externalIdentifier;
    }

    /**
     * @param $externalIdentifier
     */
    public function setExternalIdentifier($externalIdentifier): void
    {
        $this->externalIdentifier = $externalIdentifier;
    }

    /**
     * @return int
     */
    public function getInternalIdentifier()
    {
        return $this->internalIdentifier;
    }

    /**
     * @param $internalIdentifier
     */
    public function setInternalIdentifier($internalIdentifier): void
    {
        $this->internalIdentifier = $internalIdentifier;
    }

    /**
     * @return string
     */
    public function getParentIdentifier()
    {
        return $this->parentIdentifier;
    }

    /**
     * @param $parentIdentifier
     */
    public function setParentIdentifier($parentIdentifier): void
    {
        $this->parentIdentifier = $parentIdentifier;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param $description
     */
    public function setDescription($description): void
    {
        $this->description = $description;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param $position
     */
    public function setPosition($position): void
    {
        $this->position = $position;
    }

    /**
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param $active
     */
    public function setActive($active): void
    {
        $this->active = $active;
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param $image
     */
    public function setImage($image): void
    {
        $this->image = $image;
    }


}
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

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getExternalIdentifier()
    {
        return $this->externalIdentifier;
    }

    /**
     * @param mixed $externalIdentifier
     */
    public function setExternalIdentifier($externalIdentifier): void
    {
        $this->externalIdentifier = $externalIdentifier;
    }

    /**
     * @return mixed
     */
    public function getInternalIdentifier()
    {
        return $this->internalIdentifier;
    }

    /**
     * @param mixed $internalIdentifier
     */
    public function setInternalIdentifier($internalIdentifier): void
    {
        $this->internalIdentifier = $internalIdentifier;
    }

    /**
     * @return mixed
     */
    public function getParentIdentifier()
    {
        return $this->parentIdentifier;
    }

    /**
     * @param mixed $parentIdentifier
     */
    public function setParentIdentifier($parentIdentifier): void
    {
        $this->parentIdentifier = $parentIdentifier;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description): void
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param mixed $position
     */
    public function setPosition($position): void
    {
        $this->position = $position;
    }

    /**
     * @return mixed
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param mixed $active
     */
    public function setActive($active): void
    {
        $this->active = $active;
    }

    /**
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param mixed $image
     */
    public function setImage($image): void
    {
        $this->image = $image;
    }


}
<?php

namespace abaccAfterbuy\ValueObjects;

class Category extends AbstractValueObject
{
    /**
     * @var string $name
     */
    private $name;

    /**
     * we cannot define external identifier types, we have to handle those as strings
     *
     * @var string $externalIdentifier
     */
    private $externalIdentifier;

    /**
     * integer works with category ids, articles use strings (ordernumber)
     *
     * @var string $internalIdentifier
     */
    private $internalIdentifier;

    /**
     * in that case we do refer the external id
     *
     * @var string $parentIdentifier
     */
    private $parentIdentifier;

    /**
     * metadescription
     *
     * @var string $description
     */
    private $description = '';

    /**
     * @var string $position
     */
    private $position = 0;

    /**
     * @var bool $active
     */
    private $active;

    /**
     * @var string $image
     */
    private $image = '';

    /** @var string */
    private $cmsText = '';

    /** @var string */
    private $path = '';

    /**
     * @return string
     */
    public function getName(): string
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
    public function getExternalIdentifier(): ?string
    {
        return $this->externalIdentifier;
    }

    /**
     * @param $externalIdentifier
     */
    public function setExternalIdentifier(?string $externalIdentifier): void
    {
        $this->externalIdentifier = $externalIdentifier;
    }

    /**
     * @return string
     */
    public function getInternalIdentifier(): string
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
    public function getParentIdentifier(): string
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
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param $description
     */
    public function setDescription(?string $description): void
    {
        if ($description !== null) {
            $this->description = $description;
        }
    }

    /**
     * @return string
     */
    public function getPosition(): string
    {
        return $this->position;
    }

    /**
     * @param string $position
     */
    public function setPosition(?string $position): void
    {
        if ($position !== null) {
            $this->position = $position;
        }
    }

    /**
     * @return bool
     */
    public function getActive(): bool
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
    public function getImage(): string
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

    /**
     * @return string
     */
    public function getCmsText(): string
    {
        return $this->cmsText;
    }

    /**
     * @param string $cmsText
     */
    public function setCmsText(?string $cmsText): void
    {
        if ($cmsText !== null) {
           $this->cmsText = $cmsText;
        }
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath(?string $path): void
    {
        if ($path !== null) {
            $this->path = $path;
        }
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        $isValid = true;

        $isValid = $isValid && isset($this->name);
        $isValid = $isValid && isset($this->parentIdentifier);
        $isValid = $isValid && isset($this->active);

        return $isValid;
    }
}

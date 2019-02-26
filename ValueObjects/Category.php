<?php

namespace viaebShopwareAfterBuy\ValueObjects;

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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     */
    public function setName($name)
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
    public function setExternalIdentifier($externalIdentifier)
    {
        $externalIdentifier = (string) $externalIdentifier;
        $this->externalIdentifier = $externalIdentifier;
    }

    /**
     * @return string
     */
    public function getInternalIdentifier()
    {
        return $this->internalIdentifier;
    }

    /**
     * @param $internalIdentifier
     */
    public function setInternalIdentifier($internalIdentifier)
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
    public function setParentIdentifier($parentIdentifier)
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
    public function setDescription($description)
    {
        if ($description !== null) {
            $this->description = $description;
        }
    }

    /**
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param string $position
     */
    public function setPosition($position)
    {
        if ($position !== null) {
            $this->position = $position;
        }
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
    public function setActive($active)
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
    public function setImage($image)
    {
        $this->image = $image;
    }

    /**
     * @return string
     */
    public function getCmsText()
    {
        return $this->cmsText;
    }

    /**
     * @param string $cmsText
     */
    public function setCmsText($cmsText)
    {
        if ($cmsText !== null) {
           $this->cmsText = $cmsText;
        }
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        if ($path !== null) {
            $this->path = $path;
        }
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        $isValid = true;

        $isValid = $isValid && isset($this->name);
        $isValid = $isValid && isset($this->parentIdentifier);
        $isValid = $isValid && isset($this->active);

        return $isValid;
    }
}

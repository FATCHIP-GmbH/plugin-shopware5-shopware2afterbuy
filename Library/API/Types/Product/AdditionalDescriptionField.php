<?php

namespace Fatchip\Afterbuy\Types\Product;

class AdditionalDescriptionField
{
    /** @var string */
    private $FieldIDIdent;
    /** @var string */
    private $FieldNameIdent;

    /** @var int */
    private $FieldID;
    /** @var string */
    private $FieldName;
    /** @var string */
    private $FieldLabel;
    /** @var string */
    private $FieldContent;

    /**
     * @return string
     */
    public function getFieldIDIdent()
    {
        return $this->FieldIDIdent;
    }

    /**
     * @param string $FieldIDIdent
     * @return AdditionalDescriptionField
     */
    public function setFieldIDIdent($FieldIDIdent)
    {
        $this->FieldIDIdent = $FieldIDIdent;
        return $this;
    }

    /**
     * @return string
     */
    public function getFieldNameIdent()
    {
        return $this->FieldNameIdent;
    }

    /**
     * @param string $FieldNameIdent
     * @return AdditionalDescriptionField
     */
    public function setFieldNameIdent($FieldNameIdent)
    {
        $this->FieldNameIdent = $FieldNameIdent;
        return $this;
    }

    /**
     * @return int
     */
    public function getFieldID()
    {
        return $this->FieldID;
    }

    /**
     * @param int $FieldID
     * @return AdditionalDescriptionField
     */
    public function setFieldID($FieldID)
    {
        $this->FieldID = $FieldID;
        return $this;
    }

    /**
     * @return string
     */
    public function getFieldName()
    {
        return $this->FieldName;
    }

    /**
     * @param string $FieldName
     * @return AdditionalDescriptionField
     */
    public function setFieldName($FieldName)
    {
        $this->FieldName = $FieldName;
        return $this;
    }

    /**
     * @return string
     */
    public function getFieldLabel()
    {
        return $this->FieldLabel;
    }

    /**
     * @param string $FieldLabel
     * @return AdditionalDescriptionField
     */
    public function setFieldLabel($FieldLabel)
    {
        $this->FieldLabel = $FieldLabel;
        return $this;
    }

    /**
     * @return string
     */
    public function getFieldContent()
    {
        return $this->FieldContent;
    }

    /**
     * @param string $FieldContent
     * @return AdditionalDescriptionField
     */
    public function setFieldContent($FieldContent)
    {
        $this->FieldContent = $FieldContent;
        return $this;
    }
}

<?php

namespace Fatchip\Afterbuy\Types\Product;

class AdditionalDescriptionFields
{
    /** @var AdditionalDescriptionField[] */
    private $AdditionalDescriptionField;

    /**
     * @return AdditionalDescriptionField[]
     */
    public function getAdditionalDescriptionField()
    {
        return $this->AdditionalDescriptionField;
    }

    /**
     * @param AdditionalDescriptionField[] $AdditionalDescriptionField
     * @return AdditionalDescriptionFields
     */
    public function setAdditionalDescriptionField($AdditionalDescriptionField)
    {
        $this->AdditionalDescriptionField = $AdditionalDescriptionField;
        return $this;
    }
}

<?php

namespace Fatchip\Afterbuy;

use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class Normalizer extends ObjectNormalizer
{
    public function normalize($object, $format = null, array $context = [])
    {
        $clonedObject = clone $object;
        $attributes = $this->getAttributes($clonedObject, $format, $context);
        foreach ($attributes as $attribute) {
            $attributeValue = $this->getAttributeValue($clonedObject, $attribute, $format, $context);
            if ($attributeValue instanceof \stdClass) {
                $attributeValue = ($attributeValue !== null) ? (array) $attributeValue : null;
                $this->setAttributeValue($clonedObject, $attribute, $attributeValue, $format, $context);
            } elseif (is_resource($attributeValue)) {
                $this->setAttributeValue($clonedObject, $attribute, true, $format, $context);
            }
        }
        return parent::normalize($clonedObject, $format, $context);
    }
}

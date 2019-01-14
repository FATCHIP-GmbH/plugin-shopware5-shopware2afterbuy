<?php

namespace FatchipAfterbuy\Services\Helper;

use Shopware\Components\Model\ModelManager;
use Shopware\Components\Model\ModelEntity;
use FatchipAfterbuy\Components\Helper;


/**
 * Helper will extend this abstract helper. This class is defining the given type.
 *
 * Class AbstractHelper
 * @package FatchipAfterbuy\Services\Helper
 */
class AbstractHelper {
    /**
     * @var ModelManager
     */
    protected $entityManager;

    /**
     * @var string
     */
    protected $entity;

    /**
     * @var string
     */
    protected $entityAttributes;

    protected $attributeGetter;

    /**
     * @param ModelManager $entityManager
     * @param string $entity
     * @param string $entityAttributes
     * @param string $attributeGetter
     */
    public function __construct(ModelManager $entityManager, string $entity, string $entityAttributes, string $attributeGetter) {
        $this->entityManager = $entityManager;
        $this->entity = $entity;
        $this->entityAttributes = $entityAttributes;
        $this->attributeGetter = $attributeGetter;
    }

    /**
     *
     * @param string $identifier
     * @param string $field
     * @param bool $isAttribute
     * @return ModelEntity|null
     */
    public function getEntity(string $identifier, string $field, $isAttribute = false, $create = true) {
        if($isAttribute === true) {
            $entity = $this->getEntityByAttribute($identifier, $field);
        }
        else {
            $entity = $this->getEntityByField($identifier, $field);
        }

        if(!$entity && $create === true) {
            $entity = $this->createEntity($identifier, $field, $isAttribute);
        }

        return $entity;
    }

    /**
     * @param string $identifier
     * @param string $field
     * @return ModelEntity|null
     */
    public function getEntityByField(string $identifier, string $field) {
        return $this->entityManager->getRepository($this->entity)->findOneBy(array($field => $identifier));
    }

    /**
     *
     * @param string $identifier
     * @param string $field
     * @return |null
     */
    public function getEntityByAttribute(string $identifier, string $field) {
        $attribute = $this->entityManager->getRepository($this->entityAttributes)->findOneBy(array($field => $identifier));

        if($attribute === null) {
            return null;
        }

        $attributeGetter = $this->attributeGetter;

        return $attribute->$attributeGetter();
    }

    /**
     * @param string $identifier
     * @param string $field
     * @param bool $isAttribute
     * @return ModelEntity
     */
    public function createEntity(string $identifier, string $field, $isAttribute = false) {
        $entity = new $this->entity();

        //we have to create attributes manually
        $attribute = new $this->entityAttributes();
        $entity->setAttribute($attribute);

        $this->setIdentifier($identifier, $field, $entity, $isAttribute);

        return $entity;
    }

    /**
     * @param string $identifier
     * @param string $field
     * @param ModelEntity $entity
     * @param $isAttribute
     */
    public function setIdentifier(string $identifier, string $field, ModelEntity $entity, $isAttribute) {

        $setter = Helper::getSetterByField($field);

        if($isAttribute) {
            $entity->getAttribute()->$setter($identifier);
        } else {
            $entity->$setter($identifier);
        }
    }

}
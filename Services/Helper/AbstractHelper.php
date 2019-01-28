<?php

namespace FatchipAfterbuy\Services\Helper;

use Doctrine\ORM\OptimisticLockException;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Model\ModelEntity;
use FatchipAfterbuy\Components\Helper;
use DateTime;
use Exception;
use Shopware\Bundle\MediaBundle\MediaService;
use Shopware\Models\Media\Album;
use Shopware\Models\Media\Media;
use Shopware\Models\Media\Repository;

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

    /**
     * @var string
     */
    protected $attributeGetter;

    /**
     * @var
     */
    protected $taxes;

    protected $db;

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

    public function initDb(\Enlight_Components_Db_Adapter_Pdo_Mysql $db) {
        $this->db = $db;
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
     * @param float $rate
     * @return mixed
     */
    public function getTax(float $rate) {

        $rate = number_format($rate, 2);

        if(!$this->taxes) {
            $this->getTaxes();
        }

        if(array_key_exists((string) $rate, $this->taxes)) {
            return $this->taxes[$rate];
        }

        $this->createTax($rate);
        $this->getTaxes();
    }

    /**
     *
     */
    public function getTaxes() {
        $taxes = $this->entityManager->createQueryBuilder()
            ->select('taxes')
            ->from('\Shopware\Models\Tax\Tax', 'taxes', 'taxes.tax')
            ->getQuery()
            ->getResult();

        $this->taxes = $taxes;
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

    /**
     * @param $name
     * @param $url
     * @param $albumName
     *
     * @return Media
     */
    public function createMediaImage($name, $url, $albumName): Media
    {
        $path = 'media/image/' . $name . '.jpg';
        $contents = file_get_contents($url);

        /** @var MediaService $mediaService */
        $mediaService = Shopware()->Container()->get('shopware_media.media_service');
        $mediaService->write($path, $contents);

        /** @var ModelManager $models */
        $models = Shopware()->Container()->get('models');
        /** @var Repository $albumRepo */
        $albumRepo = $models->getRepository(Album::class);
        /** @var Album $album */
        $album = $albumRepo->findOneBy(['name' => $albumName]);
        // TODO: handle missing album

        $media = new Media();
        $media->setAlbumId($album->getId());
        $media->setDescription('');
        try {
            $media->setCreated(new DateTime());
        } catch (Exception $e) {
            // TODO: handle exception
        }
        $media->setUserId(0);
        $media->setName($name);
        $media->setPath($path);
        $media->setFileSize($mediaService->getSize($path));
        $media->setExtension('jpg');
        $media->setType('image');

        $this->entityManager->persist($media);
        try {
            $this->entityManager->flush($media);
        } catch (OptimisticLockException $e) {
        }

        return $media;
    }
}
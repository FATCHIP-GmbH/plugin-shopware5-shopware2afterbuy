<?php

namespace FatchipAfterbuy\Services\Helper;

use Doctrine\ORM\OptimisticLockException;
use Psr\Log\LoggerInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Model\ModelEntity;
use FatchipAfterbuy\Components\Helper;
use DateTime;
use Exception;
use Shopware\Bundle\MediaBundle\MediaService;
use Shopware\Components\Model\ModelRepository;
use Shopware\Models\Media\Album;
use Shopware\Models\Media\Media;
use Shopware\Models\Media\Repository as MediaRepository;
use Shopware\Models\Tax\Tax;

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

    protected $mediaService;

    protected $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger) {
        $this->logger = $logger;
    }

    /**
     * @param ModelManager $entityManager
     * @param string $entity
     * @param string $entityAttributes
     * @param string $attributeGetter
     */
    public function __construct(ModelManager $entityManager, $entity = '', $entityAttributes = '', $attributeGetter = '') {
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
    public function createTax(float $rate) {
        $tax = new Tax();
        $tax->setTax($rate);
        $tax->setName($rate);

        $this->entityManager->persist($tax);
        $this->entityManager->flush();
    }

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

    public function initMediaService(MediaService $mediaService) {
        $this->mediaService = $mediaService;
    }

    /**
     * @param $url
     * @param $albumName
     *
     * @return Media
     */
    public function createMediaImage($url, $albumName): ?Media
    {
        $path_info = pathinfo($url);
        $filename = $this->filterNotAllowedCharactersFromURL($path_info['filename']);
        $path = 'media/image/' . $filename . '.' . $path_info['extension'];

        if ( ! $contents = file_get_contents($url)) {
            return null;
        }

        /** @var MediaService $mediaService */
        $mediaService = Shopware()->Container()->get('shopware_media.media_service');
        /** @var MediaRepository $mediaRepo */
        $mediaRepo = $this->entityManager->getRepository(Media::class);
        $medias = $mediaRepo->getMediaByPathQuery($path)->getResult();

        if (count($medias) > 0) {
            return $medias[0];
        }

        $mediaService->write($path, $contents);

        /** @var ModelRepository $albumRepo */
        $albumRepo = $this->entityManager->getRepository(Album::class);
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
        $media->setAlbum($album);
        $media->setUserId(0);
        $media->setName($filename);
        $media->setPath($path);
        $media->setFileSize($mediaService->getSize($path));
        $media->setExtension($path_info['extension']);
        $media->setType(Media::TYPE_IMAGE);

        $this->entityManager->persist($media);
        try {
            $this->entityManager->flush($media);
        } catch (OptimisticLockException $e) {
        }

        if ($media->getType() === Media::TYPE_IMAGE && ! in_array($media->getExtension(), ['tif', 'tiff'], true)
        ) {
            $manager = Shopware()->Container()->get('thumbnail_manager');
            $manager->createMediaThumbnail($media, [], true);
        }

        return $media;
    }

    /**
     * @param string $url
     *
     * @return string
     */
    public function filterNotAllowedCharactersFromURL(string $url): string
    {
        $badCharacters = '()+.';

        $urlArray = str_split($url);

        foreach ($urlArray as $index => $character) {
            if (strpos($badCharacters, $character) !== false) {
                $url[$index] = '_';
            }
        }

        return $url;
    }
}
<?php
/** @noinspection SpellCheckingInspection */

namespace viaebShopwareAfterbuy\Services\Helper;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Enlight_Components_Db_Adapter_Pdo_Mysql;
use League\Flysystem\Util;
use Psr\Log\LoggerInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Model\ModelEntity;
use Shopware\Components\Plugin\CachedConfigReader;
use viaebShopwareAfterbuy\Components\Helper;
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
 * @package viaebShopwareAfterbuy\Services\Helper
 */
class AbstractHelper
{
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

    /** @var Enlight_Components_Db_Adapter_Pdo_Mysql */
    protected $db;

    /** @var MediaService */
    protected $mediaService;

    /** @var LoggerInterface */
    protected $logger;

    /**
     * @var Connection
     */
    protected $dbal;

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /** @var array */
    public $config;

    private $imageMimeTypes =
        [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/bmp',
            'image/tiff',
            'image/svg+xml',
            'image/svg',
        ];

    /**
     * @param ModelManager $entityManager
     * @param string       $entity
     * @param string       $entityAttributes
     * @param string       $attributeGetter
     */
    public function __construct(
        ModelManager $entityManager,
        $entity = '',
        $entityAttributes = '',
        $attributeGetter = ''
    ) {
        $this->entityManager = $entityManager;
        $this->entity = $entity;
        $this->entityAttributes = $entityAttributes;
        $this->attributeGetter = $attributeGetter;
    }

    /**
     * @param Enlight_Components_Db_Adapter_Pdo_Mysql $db
     */
    public function initDb(Enlight_Components_Db_Adapter_Pdo_Mysql $db)
    {
        $this->db = $db;
    }

    /**
     * @param Connection $dbal
     */
    public function initDbal(Connection $dbal)
    {
        $this->dbal = $dbal;
    }

    /**
     * @param CachedConfigReader $configReader
     * @param string $pluginName
     */
    public function setConfig(CachedConfigReader $configReader, string $pluginName)
    {
        $this->config = $configReader->getByPluginName($pluginName);
    }

    /**
     *
     * @param string $identifier
     * @param string $field
     * @param bool   $isAttribute
     * @param bool   $create
     *
     * @return ModelEntity|null
     */
    public function getEntity(string $identifier, string $field, $isAttribute = false, $create = true)
    {
        if ($isAttribute === true) {
            $entity = $this->getEntityByAttribute($identifier, $field);
        } else {
            $entity = $this->getEntityByField($identifier, $field);
        }

        if ( ! $entity && $create === true) {
            $entity = $this->createEntity($identifier, $field, $isAttribute);
        }

        return $entity;
    }

    /**
     * @param float $rate
     *
     * @return Tax
     */
    public function getTax(float $rate)
    {
        $rate_s = number_format($rate, 2);

        if ( ! $this->taxes) {
            $this->getTaxes();
        }

        if (array_key_exists($rate_s, $this->taxes)) {
            return $this->taxes[$rate_s];
        }

        $this->createTax($rate_s);
        $this->getTaxes();

        return null;
    }

    /**
     * @param float $rate
     */
    public function createTax(float $rate)
    {
        $tax = new Tax();
        $tax->setTax($rate);
        $tax->setName($rate);


        try {
            $this->entityManager->persist($tax);
            $this->entityManager->flush();
        } catch (OptimisticLockException $e) {
            $this->logger->error('Error saving tax rule', array($rate));
        } catch (ORMException $e) {
            $this->logger->error('Error saving tax rule', array($rate));
        }
    }

    /**
     *
     */
    public function getTaxes()
    {
        $taxes = $this->entityManager->createQueryBuilder()
            ->select('taxes')
            ->from(Tax::class, 'taxes', 'taxes.tax')
            ->getQuery()
            ->getResult();

        $this->taxes = $taxes;
    }

    /**
     * @param string $identifier
     * @param string $field
     *
     * @return object|ModelEntity|null
     */
    public function getEntityByField(string $identifier, string $field)
    {
        return $this->entityManager->getRepository($this->entity)->findOneBy(array($field => $identifier));
    }

    /**
     *
     * @param string $identifier
     * @param string $field
     *
     * @return object|null
     */
    public function getEntityByAttribute(string $identifier, string $field)
    {
        $attribute =
            $this->entityManager->getRepository($this->entityAttributes)->findOneBy(array($field => $identifier));

        if ($attribute === null) {
            return null;
        }

        $attributeGetter = $this->attributeGetter;

        return $attribute->$attributeGetter();
    }

    /**
     * @param string $identifier
     * @param string $field
     * @param bool   $isAttribute
     *
     * @return ModelEntity
     */
    public function createEntity(string $identifier, string $field, $isAttribute = false)
    {
        $entity = new $this->entity();

        //we have to create attributes manually
        $attribute = new $this->entityAttributes();

        /**
         * setAttribute is implemented for each entity specifically
         * @noinspection PhpUndefinedMethodInspection
         */
        $entity->setAttribute($attribute);

        $this->setIdentifier($identifier, $field, $entity, $isAttribute);

        return $entity;
    }

    /**
     * @param string      $identifier
     * @param string      $field
     * @param ModelEntity $entity
     * @param             $isAttribute
     */
    public function setIdentifier(string $identifier, string $field, ModelEntity $entity, $isAttribute)
    {

        $setter = Helper::getSetterByField($field);

        if ($isAttribute) {
            /**
             * setAttribute is implemented for each entity specifically
             * @noinspection PhpUndefinedMethodInspection
             */
            $entity->getAttribute()->$setter($identifier);
        } else {
            $entity->$setter($identifier);
        }
    }

    /**curl_close($ch)
     * @param $url
     *
     * @return bool|string
     */
    public function grab_image($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);

        /**
         * Follow redirects to prevent downloading html redirects as an image
         */
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        /**
         * this enables self signed ssl certificates.
         * as long requests are possible via http, mitm-attacks  will always be a problem
         * @noinspection CurlSslServerSpoofingInspection
         */
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        /** @noinspection CurlSslServerSpoofingInspection */
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $raw = curl_exec($ch);

        // uncomment to debug redirects and compare download url with redirect url
        // $redirectURL = curl_getinfo($ch,CURLINFO_EFFECTIVE_URL );
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $errorOccured = (
            $error = (curl_error($ch) ||
                $status === 404)
        );

        if ($errorOccured) {
            $this->logger->error('Error Downloading Image: ' . $error, array($url, $status));
            return false;
        }

        curl_close($ch);

        return $raw;
    }

    /**
     * @param MediaService $mediaService
     */
    public function initMediaService(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    /**
     * @param $url
     * @param $albumName
     *
     * @return Media
     */
    public function createMediaImage($url, $albumName = 'Artikel')
    {
        if ( ! $url) {
            return null;
        }

        $path_info = pathinfo($url);
        $filename = $this->filterNotAllowedCharactersFromURL($path_info['filename']);

        if(!array_key_exists('extension', $path_info)) {
            return null;
        }

        $path = 'media/image/' . $filename . '.' . $path_info['extension'];

        if ( ! $contents = $this->grab_image($url)) {
            return null;
        }

        // check if content is really an image to prevent later errors
        $mimeType = Util::guessMimeType($path, $contents);

        if (! in_array($mimeType, $this->imageMimeTypes, true)) {
            $this->logger->error('Error: Image file contents does not seem to contain a picture. Content matches ' . $mimeType, array($url));
            echo 'Error: Image file contents does not seem to be a picture. Content matches ' . $mimeType .PHP_EOL;
            return null;
        } else {
            $this->logger->info('Info: Image file content matches ' .$mimeType, array($url));
            // echo 'Info: Image file content matches ' . $mimeType . PHP_EOL;
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

        if(!$album === null) {
            return null;
        }

        $filesize = $mediaService->getSize($path);

        $media = new Media();
        $media->setAlbumId($album->getId());
        $media->setDescription('');
        try {
            $media->setCreated(new DateTime());
        } catch (Exception $e) {
            $this->logger->error('Error while creating media', array($url));
        }
        $media->setAlbum($album);
        $media->setUserId(0);
        $media->setName($filename);
        $media->setPath($path);
        $media->setFileSize($filesize);
        $media->setExtension($path_info['extension']);
        $media->setType(Media::TYPE_IMAGE);

        try {
            $this->entityManager->persist($media);
            $this->entityManager->flush($media);
        } catch (OptimisticLockException $e) {
            $this->logger->error($e->getMessage());
        } catch (ORMException $e) {
            $this->logger->error($e->getMessage());
        }

        if ($media->getType() === Media::TYPE_IMAGE && ! in_array($media->getExtension(), ['tif', 'tiff'], true)
        ) {
            $manager = Shopware()->Container()->get('thumbnail_manager');
            $manager->createMediaThumbnail($media, [], true);
        }

        return $media;
    }

    /**
     * @return mixed|string
     */
    public static function getShopwareVersion() {
        /** @noinspection DuplicatedCode */
        $currentVersion = '';

        if(defined('\Shopware::VERSION')) {
            /** @noinspection PhpFullyQualifiedNameUsageInspection */
            /** @noinspection PhpUndefinedClassConstantInspection */
            $currentVersion = \Shopware::VERSION;
        }

        //get old composer versions
        if($currentVersion === '___VERSION___' && class_exists('ShopwareVersion') && class_exists('PackageVersions\Versions')) {
            /** @noinspection PhpFullyQualifiedNameUsageInspection */
            /** @noinspection PhpUndefinedClassConstantInspection */
            /** @noinspection PhpUndefinedClassInspection */
            $currentVersion = \ShopwareVersion::parseVersion(
                \PackageVersions\Versions::getVersion('shopware/shopware')
            )['version'];
        }

        if(!$currentVersion || $currentVersion === '___VERSION___') {
            $currentVersion = Shopware()->Container()->getParameter('shopware.release.version');
        }

        return $currentVersion;
    }

    /**
     * @param string $url
     *
     * @return string
     */
    public function filterNotAllowedCharactersFromURL(string $url)
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
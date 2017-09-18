<?php

use Doctrine\Common\Annotations\AnnotationReader;
use GuzzleHttp\Exception\ClientException;
use Shopware\Components\CSRFWhitelistAware;
use Shopware\CustomModels\FatchipShopware2Afterbuy\PluginConfig;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Backend controller
 */
class Shopware_Controllers_Backend_FatchipShopware2AfterbuyAdmin extends Enlight_Controller_Action implements CSRFWhitelistAware
{
    /** @var string $configModel */
    protected $configModel = 'Shopware\CustomModels\FatchipShopware2Afterbuy\PluginConfig';

    /**
     * @return string[]
     */
    public function getWhitelistedCSRFActions()
    {
        return ['index'];
    }

    public function indexAction()
    {
    }

     public function pluginConfigAction()
    {
        $context = [
            'module' => 'pluginConfig',
            'maxHeight' => $this->View()->getAssign('maxHeight') ?: 925,
            'config' => $this->get('models')->createQueryBuilder()
                ->select('c')->from($this->configModel, 'c')->where('c.id = 1')
                ->getQuery()->execute()[0],
        ];
        $this->View()->assign($context);
    }

}

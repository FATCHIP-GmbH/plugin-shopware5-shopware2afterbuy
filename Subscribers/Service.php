<?php

namespace Shopware\viaebShopware2Afterbuy\Subscribers;

use Enlight\Event\SubscriberInterface;
use Fatchip\Afterbuy\ApiClient;
use Shopware\CustomModels\viaebShopware2Afterbuy\PluginConfig;

/**
 * Class Service
 *
 * @package Shopware\viaebShopware2Afterbuy\Subscribers
 */
class Service implements SubscriberInterface
{
    /**
     * Returns the subscribed events
     *
     * @return string[]
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Bootstrap_InitResource_fatchip_shopware2afterbuy_api_client' =>
                'onInitApiLegacyClient',
            'Enlight_Bootstrap_InitResource_afterbuy_api_client' =>
                'onInitApiClient',
        ];
    }

    /**
     * @return ApiClient
     * @throws \Exception
     */
    public function onInitApiClient()
    {
        /** @var  PluginConfig $config */
        $config = Shopware()
            ->Models()
            ->getRepository('Shopware\CustomModels\viaebShopware2Afterbuy\PluginConfig')
            ->find(1);

        if (!$config) {
            throw new \Exception('Plugin configuration could not be found!');
        }

        return new ApiClient($config->toCompatArray());
    }

    /**
     * @return \fcafterbuyapi
     * @throws \Exception
     */
    public function onInitApiLegacyClient()
    {
        require_once __DIR__ .  DIRECTORY_SEPARATOR . '../Components/LegacyApi/fcafterbuyapi.php';
        require_once __DIR__ .  DIRECTORY_SEPARATOR . '../Components/LegacyApi/fcafterbuyart.php';
        require_once __DIR__ .  DIRECTORY_SEPARATOR . '../Components/LegacyApi/fcafterbuyorder.php';

        /** @var  PluginConfig $configObject */
        $configObject = Shopware()
            ->Models()
            ->getRepository('Shopware\CustomModels\viaebShopware2Afterbuy\PluginConfig')
            ->find(1);
        // Todo better error handling returns (No service Returned Exception)
        if (!$configObject){
            return null;
        }
        $configArray = $configObject->toCompatArray();
        return new \fcafterbuyapi($configArray);
    }
}

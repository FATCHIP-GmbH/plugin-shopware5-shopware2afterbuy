<?php

namespace Shopware\FatchipShopware2Afterbuy\Subscribers;

use Enlight\Event\SubscriberInterface;
use Shopware\CustomModels\FatchipShopware2Afterbuy\PluginConfig;

/**
 * Class Service
 *
 * @package Shopware\FatchipShopware2Afterbuy\Subscribers
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
                'onInitApiClient',
        ];
    }

    /**
     * @return \fcafterbuyapi
     */
    public function onInitApiClient()
    {
        require_once __DIR__ .  DIRECTORY_SEPARATOR . '../Components/Api/fcafterbuyapi.php';
        require_once __DIR__ .  DIRECTORY_SEPARATOR . '../Components/Api/fcafterbuyart.php';
        require_once __DIR__ .  DIRECTORY_SEPARATOR . '../Components/Api/fcafterbuyorder.php';

        /** @var  PluginConfig $configObject */
        $configObject = Shopware()
            ->Models()
            ->getRepository('Shopware\CustomModels\FatchipShopware2Afterbuy\PluginConfig')
            ->find(1);
        // Todo better error handling returns (No service Returned Exception)
        if (!$configObject){
            return;
        }
        $configArray = $configObject->toCompatArray();
        return new \fcafterbuyapi($configArray);
    }
}

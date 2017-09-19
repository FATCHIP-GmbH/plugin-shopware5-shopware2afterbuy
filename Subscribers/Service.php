<?php

namespace Shopware\FatchipShopware2Afterbuy\Subscribers;

use Enlight\Event\SubscriberInterface;
use Shopware\CustomModels\FatchipShopware2Afterbuy\PluginConfig;
use Shopware\FatchipShopware2Afterbuy\Components\Api\fcafterbuyapi;

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
            'Enlight_Bootstrap_InitResource_fatchip_shopware2Afterbuy_api_client' =>
                'onInitApiClient',
        ];
    }

    /**
     * @return fcafterbuyapi
     */
    public function onInitApiClient()
    {
        /** @var  PluginConfig $configObject */
        $configObject = Shopware()
            ->Models()
            ->getRepository('Shopware\CustomModels\FatchipShopware2Afterbuy\PluginConfig')
            ->find(1);
        $configArray = $configObject->toCompatArray();
        return new fcafterbuyapi($configArray);
    }
}

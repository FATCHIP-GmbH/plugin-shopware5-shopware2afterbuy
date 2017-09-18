<?php

namespace Shopware\FatchipShopware2Afterbuy\Subscribers;

use Enlight\Event\SubscriberInterface;

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
     * @return ApiClient
     */
    public function onInitApiClient()
    {
        return new ApiClient(
            Shopware()
                ->Models()
                ->getRepository('Shopware\CustomModels\FatchipShopware2Afterbuy\PluginConfig')
                ->find(1)
        );
    }
}

<?php

namespace Shopware\FatchipShopware2Afterbuy\Subscribers;

use Enlight\Event\SubscriberInterface;

/**
 * Class ControllerPath
 *
 * @package Shopware\FatchipShopware2Afterbuy\Subscribers
 */
class ControllerPath implements SubscriberInterface
{
    /**
     * Returns the subscribed events
     *
     * @return string[]
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_FatchipShopware2AfterbuyAdmin' =>
                'onGetControllerPathBackendAdmin',
        ];
    }

    /**
     * Registers the backend controller for the admin application
     *
     * @param \Enlight_Event_EventArgs $args
     * @return string
     * @Enlight\Event Enlight_Controller_Dispatcher_ControllerPath_Backend_FatchipShopware2AfterbuyAdmin
     */
    public function onGetControllerPathBackendAdmin(\Enlight_Event_EventArgs $args)
    {
        return __DIR__ . '/../Controllers/Backend/FatchipShopware2AfterbuyAdmin.php';
    }
}

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
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_FatchipShopware2AfterbuyTriggerCronJob'
            => 'onGetFrontendControllerPath',
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

    /**
     * Provide path to custom frontend controllers
     * @param \Enlight_Event_EventArgs $args
     * @return string
     */
    public function onGetFrontendControllerPath(\Enlight_Event_EventArgs $args)
    {
        $controllerName = $args->getRequest()->getControllerName();
        return __DIR__ . '/../Controllers/Frontend/' . $controllerName . '.php';
    }
}

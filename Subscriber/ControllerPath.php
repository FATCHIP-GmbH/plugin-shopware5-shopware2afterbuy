<?php

namespace abaccAfterbuy\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Event_EventArgs;

class ControllerPath implements SubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_abaccResetShopConnection'
            => 'getAbaccResetShopConnectionController'
        ];
    }

    public function getAbaccResetShopConnectionController(Enlight_Event_EventArgs $args)
    {
        return __DIR__ . '/../Controllers/Backend/abaccResetShopConnection.php';
    }
}

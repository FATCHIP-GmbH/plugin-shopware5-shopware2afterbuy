<?php

namespace viaebShopwareAfterbuy\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Event_EventArgs;

class ControllerPath implements SubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_viaebResetShopConnection'
            => 'getViaebResetShopConnectionController',
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_viaebConfigForm'
            => 'getViaebConfigFormController',

        ];
    }

    public function getViaebResetShopConnectionController(Enlight_Event_EventArgs $args)
    {
        return __DIR__ . '/../Controllers/Backend/viaebResetShopConnection.php';
    }

    public function getViaebConfigFormController(Enlight_Event_EventArgs $args)
    {
        return __DIR__ . '/../Controllers/Backend/viaebConfigForm.php';
    }
}

<?php
/** @noinspection SpellCheckingInspection */

namespace viaebShopwareAfterbuy\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Event_EventArgs;

/** @noinspection PhpUnused */
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

    /** @noinspection PhpUnused */
    public function getViaebResetShopConnectionController(
        /** @noinspection PhpUnusedParameterInspection */ Enlight_Event_EventArgs $args
    )
    {
        return __DIR__ . '/../Controllers/Backend/viaebResetShopConnection.php';
    }

    /** @noinspection PhpUnused */
    public function getViaebConfigFormController(
        /** @noinspection PhpUnusedParameterInspection */ Enlight_Event_EventArgs $args
    )
    {
        return __DIR__ . '/../Controllers/Backend/viaebConfigForm.php';
    }
}

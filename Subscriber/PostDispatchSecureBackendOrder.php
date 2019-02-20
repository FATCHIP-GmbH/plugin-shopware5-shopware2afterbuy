<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 19.02.19
 * Time: 14:00
 */

namespace abaccAfterbuy\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Controller_Action;
use Enlight_Event_EventArgs;

class PostDispatchSecureBackendOrder implements SubscriberInterface
{
    /**
     * @var string
     */
    private $pluginDirectory;

    /**
     * @param $pluginDirectory
     */
    public function __construct($pluginDirectory)
    {
        $this->pluginDirectory = $pluginDirectory;
    }

    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Order' =>
                'onCustomerPostDispatch',
        ];
    }

    public function onCustomerPostDispatch(Enlight_Event_EventArgs $args)
    {
        /** @var Enlight_Controller_Action $controller */
        $controller = $args->get('subject');
        $view = $controller->View();

        $view->addTemplateDir($this->pluginDirectory . '/Resources/views');

        if ($controller->Request()->getActionName() == 'load') {
            $view->extendsTemplate('backend/abacc_extend_order/view/list_view.js');
            $view->extendsTemplate('backend/abacc_extend_order/model/order_model.tpl');
        } elseif ($controller->Request()->getActionName() === 'getList') {
            $orders = $controller->View()->getAssign();

            foreach ($orders['data'] as $index => $order) {
                $orders['data'][$index]['afterbuyOrderId'] = '' . $index;
            }

            $controller->View()->assign($orders);
        }
    }
}

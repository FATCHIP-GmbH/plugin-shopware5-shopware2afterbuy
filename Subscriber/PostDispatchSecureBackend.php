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
use Enlight_View_Default;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin\CachedConfigReader;
use Shopware\Models\Order\Order;

class PostDispatchSecureBackend implements SubscriberInterface
{
    /**
     * @var ModelManager
     */
    protected $entityManager;

    /**
     * @var string
     */
    protected $pluginDirectory;

    /** @var array */
    protected $config;

    /**
     * @param ModelManager $entityManager
     * @param string $pluginDirectory
     * @param CachedConfigReader $configReader
     * @param string $pluginName
     */
    public function __construct(
        ModelManager $entityManager,
        string $pluginDirectory,
        CachedConfigReader $configReader,
        string $pluginName
    )
    {
        $this->entityManager = $entityManager;
        $this->pluginDirectory = $pluginDirectory;
        $this->config = $configReader->getByPluginName($pluginName);
    }

    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Index' => 'onPostDispatchSecureBackendIndex',
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Order' => 'onPostDispatchSecureBackendOrder',
        ];
    }

    public function onPostDispatchSecureBackendIndex(Enlight_Event_EventArgs $args)
    {
        // afterbuy is carrying system
        if ($this->config['mainSystem'] == 2) {
            return;
        }

        /** @var Enlight_View_Default $view */
        $view = $this->prepareEventHandler($args)[1];

        $view->extendsTemplate('backend/abacc_extend_order/base/header.tpl');

    }

    public function onPostDispatchSecureBackendOrder(Enlight_Event_EventArgs $args)
    {
        /** @var Enlight_Controller_Action $controller */
        list($controller, $view) = $this->prepareEventHandler($args);

        if ($controller->Request()->getActionName() == 'load') {
            $view->extendsTemplate('backend/abacc_extend_order/view/list_view.js');
            $view->extendsTemplate('backend/abacc_extend_order/model/order_model.tpl');
        } elseif ($controller->Request()->getActionName() === 'getList') {
            $orders = $controller->View()->getAssign();

            //TODO: merge outside subscriber
            foreach ($orders['data'] as $index => $order) {
                /** @var Order $currentOrder */
                $currentOrder = $this->entityManager->getRepository(Order::class)->find($order['id']);
                $orders['data'][$index]['afterbuyOrderId'] = $currentOrder->getAttribute()->getAfterbuyOrderId();
            }

            $controller->View()->assign($orders);
        }
    }

    /**
     * @param Enlight_Event_EventArgs $args
     * @return array array with current $controller and $view [$controller, $view]
     */
    public function prepareEventHandler(Enlight_Event_EventArgs $args): array
    {
        /** @var Enlight_Controller_Action $controller */
        $controller = $args->get('subject');
        $view = $controller->View();

        $view->addTemplateDir($this->pluginDirectory . '/Resources/views');
        return array($controller, $view);
    }
}

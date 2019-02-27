<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 19.02.19
 * Time: 14:00
 */

namespace viaebShopwareAfterbuy\Subscriber;

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

    /** @var Enlight_View_Default $view */
    protected $view;

    /** @var Enlight_Controller_Action $controller */
    protected $controller;

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
            'Enlight_Controller_Action_PreDispatch' => 'addTemplateDir',
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

        $this->view->extendsTemplate('backend/abacc_extend_order/base/header.tpl');

    }

    public function onPostDispatchSecureBackendOrder(Enlight_Event_EventArgs $args)
    {
        if ($this->controller->Request()->getActionName() == 'load') {
            $this->view->extendsTemplate('backend/abacc_extend_order/view/list_view.js');
            $this->view->extendsTemplate('backend/abacc_extend_order/model/order_model.tpl');
        } elseif ($this->controller->Request()->getActionName() === 'getList') {
            $orders = $this->controller->View()->getAssign();

            //TODO: merge outside subscriber
            foreach ($orders['data'] as $index => $order) {
                /** @var Order $currentOrder */
                $currentOrder = $this->entityManager->getRepository(Order::class)->find($order['id']);
                $orders['data'][$index]['afterbuyOrderId'] = $currentOrder->getAttribute()->getAfterbuyOrderId();
            }

            $this->controller->View()->assign($orders);
        }
    }

    /**
     * @param Enlight_Event_EventArgs $args
     */
    public function addTemplateDir(Enlight_Event_EventArgs $args)
    {
        $this->controller = $args->get('subject');
        $this->view = $this->controller->View();

        $this->view->addTemplateDir($this->pluginDirectory . '/Resources/views');
    }
}

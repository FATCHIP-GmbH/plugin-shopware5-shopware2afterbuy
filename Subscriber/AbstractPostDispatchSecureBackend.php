<?php
/** @noinspection SpellCheckingInspection */

namespace viaebShopwareAfterbuy\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Controller_Action;
use Enlight_Event_EventArgs;
use Enlight_View_Default;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin\CachedConfigReader;
use viaebShopwareAfterbuy\Services\Helper\AbstractHelper;

class AbstractPostDispatchSecureBackend implements SubscriberInterface
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

    /** @var AbstractHelper */
    protected $helper;

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

    public function initHelper(AbstractHelper $helper) {
        $this->helper = $helper;
    }

    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PreDispatch' => 'addTemplateDir',
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Index' => 'onPostDispatchSecureBackendIndex',
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Order' => 'onPostDispatchSecureBackendOrder',
            'Enlight_Controller_Action_PostDispatchSecure_Backend_ArticleList' => 'onPostDispatchSecureBackendArticleList',
        ];
    }


    /** @noinspection PhpUnused */
    public function onPostDispatchSecureBackendIndex(
        /** @noinspection PhpUnusedParameterInspection */ Enlight_Event_EventArgs $args
    )
    {
        // afterbuy is carrying system
        if ((int)$this->config['mainSystem'] == 2) {
            return;
        }

        $this->view->extendsTemplate('backend/viaeb_extend_order/base/header.tpl');

    }

    public function onPostDispatchSecureBackendArticleList()
    {
        if ($this->controller->Request()->getActionName() == 'load') {
            $this->view->extendsTemplate('backend/viaeb_extend_article_list/view/list_view.js');
        } elseif ($this->controller->Request()->getActionName() == 'columnConfig') {

            $columnConfig = $this->controller->View()->getAssign();

            $columnConfig = $this->helper->manipulateArticleList($columnConfig, $this->config);

            $this->controller->View()->assign($columnConfig);
        }
    }

    /** @noinspection PhpUnused */
    public function onPostDispatchSecureBackendOrder()
    {
        if ($this->controller->Request()->getActionName() == 'load') {
            $this->view->extendsTemplate('backend/viaeb_extend_order/view/list_view.js');
            $this->view->extendsTemplate('backend/viaeb_extend_order/model/order_model.tpl');
        } elseif ($this->controller->Request()->getActionName() === 'getList') {
            $orders = $this->controller->View()->getAssign();
            $orders = $this->helper->addAfterbuyOrderIdToOrders($orders);
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

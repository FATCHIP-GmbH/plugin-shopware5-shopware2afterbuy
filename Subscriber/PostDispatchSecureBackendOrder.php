<?php
/** @noinspection SpellCheckingInspection */

namespace viaebShopwareAfterbuy\Subscriber;

use viaebShopwareAfterbuy\Services\Helper\ShopwareOrderHelper;

class PostDispatchSecureBackendOrder extends AbstractPostDispatchSecureBackend
{
    /** @noinspection PhpUnused */
    public function onPostDispatchSecureBackendOrder()
    {
        if ($this->controller->Request()->getActionName() == 'load') {
            $this->view->extendsTemplate('backend/viaeb_extend_order/view/list_view.js');
            $this->view->extendsTemplate('backend/viaeb_extend_order/model/order_model.tpl');
        } elseif ($this->controller->Request()->getActionName() === 'getList') {
            /** @var ShopwareOrderHelper $orderHelper */
            $orderHelper = $this->helper;

            $orders = $this->controller->View()->getAssign();

            $orders = $orderHelper->addAfterbuyOrderIdToOrders($orders);

            $this->controller->View()->assign($orders);
        }
    }
}

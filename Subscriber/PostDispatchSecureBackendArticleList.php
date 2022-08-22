<?php
/** @noinspection SpellCheckingInspection */

namespace viaebShopwareAfterbuy\Subscriber;

use viaebShopwareAfterbuy\Services\Helper\ShopwareArticleHelper;

/**
 * Class PostDispatchSecureBackendArticleList
 * @package viaebShopwareAfterbuy\Subscriber
 * @property ShopwareArticleHelper $helper
 */
class PostDispatchSecureBackendArticleList extends AbstractPostDispatchSecureBackend
{
    /** @noinspection PhpUnused */
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
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 19.02.19
 * Time: 14:00
 */

namespace viaebShopwareAfterbuy\Subscriber;

use viaebShopwareAfterbuy\Services\Helper\ShopwareArticleHelper;

class PostDispatchSecureBackendArticleList extends AbstractPostDispatchSecureBackend
{

    public function onPostDispatchSecureBackendArticleList()
    {
        if ($this->controller->Request()->getActionName() == 'load') {
            $this->view->extendsTemplate('backend/viaeb_extend_article_list/view/list_view.js');
        } elseif ($this->controller->Request()->getActionName() == 'columnConfig') {
            /** @var ShopwareArticleHelper $orderHelper */
            $orderHelper = $this->helper;

            $columnConfig = $this->controller->View()->getAssign();

            $columnConfig = $orderHelper->manipulateArticleList($columnConfig);

            $this->controller->View()->assign($columnConfig);
        }
    }
}

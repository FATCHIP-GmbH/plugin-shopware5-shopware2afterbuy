<?php
/** @noinspection SpellCheckingInspection */

use viaebShopwareAfterbuy\Services\Helper\ShopwareResetHelper;

/** @noinspection PhpUnused */
class Shopware_Controllers_Backend_viaebResetShopConnection extends Shopware_Controllers_Backend_ExtJs
{
    public function indexAction()
    {
    }

    /** @noinspection PhpUnused */
    public function resetAction()
    {
        /** @var ShopwareResetHelper $shopwareResetHelper */
        $shopwareResetHelper = Shopware()->Container()->get(
            'viaeb_shopware_afterbuy.services.helper.shopware_reset_helper'
        );

        $result = $shopwareResetHelper->resetShopConnection();

        $this->View()->assign([
            'success' => $result['msg'] === 'success',
            'data' => $result['data'],
            'total' => count($result['data']),
        ]);
    }
}

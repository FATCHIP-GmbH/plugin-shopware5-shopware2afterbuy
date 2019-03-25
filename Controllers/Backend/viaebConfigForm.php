<?php


class Shopware_Controllers_Backend_viaebConfigForm extends Shopware_Controllers_Backend_ExtJs
{
    /** @var \Shopware\Components\ConfigWriter $configWriter */
    protected $configWriter;

    protected $pluginName;

    /** @var \Shopware\Components\CacheManager */
    protected $cacheManager;

    /** @var \viaebShopwareAfterbuy\Services\Helper\ShopwareConfigHelper */
    protected $configHelper;

    public function init()
    {
        parent::init();

        $this->configWriter = Shopware()->Container()->get('config_writer');
        $this->pluginName = Shopware()->Container()->getParameter('viaeb_shopware_afterbuy.plugin_name');
        $this->cacheManager = Shopware()->Container()->get('shopware.cache_manager');
        $this->configHelper = Shopware()->Container()->get('viaeb_shopware_afterbuy.services.helper.shopware_config_helper');
    }

    public function getConfigValuesAction() {
        $config = $this->configHelper->getConfigValues($this->pluginName);

        $this->view->assign([
            'success' => true,
            'data' => $config
        ]);
    }

    public function saveConnectionConfigAction() {

        $this->View()->assign([
            'success' => true,
        ]);

        try{
            $this->configWriter->save('partnerId', $_REQUEST['partnerId'], $this->pluginName);
            $this->configWriter->save('partnerPassword', $_REQUEST['partnerPassword'], $this->pluginName);
            $this->configWriter->save('userName', $_REQUEST['userName'], $this->pluginName);
            $this->configWriter->save('userPassword', $_REQUEST['userPassword'], $this->pluginName);
            $this->configWriter->save('mainSystem', intval($_REQUEST['mainSystem']), $this->pluginName);
            $this->configWriter->save('baseCategory', intval($_REQUEST['baseCategory']), $this->pluginName);
            $this->configWriter->save('ExportAllArticles', intval($_REQUEST['ExportAllArticles']), $this->pluginName);
            $this->configWriter->save('targetShop', intval($_REQUEST['targetShop']), $this->pluginName);
            $this->configWriter->save('shipping', intval($_REQUEST['shipping']), $this->pluginName);
            $this->configWriter->save('customerGroup', intval($_REQUEST['customerGroup']), $this->pluginName);
        }
        catch (Exception $e) {
            $this->View()->assign([
                'success' => false,
            ]);
        }

        $this->cacheManager->clearConfigCache();
    }
}

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
            $this->configWriter->save('ordernumberMapping', intval($_REQUEST['ordernumberMapping']), $this->pluginName);

            $this->configWriter->save('minOrderDate', str_replace(" ", "T", $_REQUEST['minOrderDate']), $this->pluginName);

            $this->configWriter->save('baseCategory', intval($_REQUEST['baseCategory']), $this->pluginName);
            $this->configWriter->save('ExportAllArticles', intval($_REQUEST['ExportAllArticles']), $this->pluginName);
            $this->configWriter->save('targetShop', intval($_REQUEST['targetShop']), $this->pluginName);
            $this->configWriter->save('shipping', intval($_REQUEST['shipping']), $this->pluginName);
            $this->configWriter->save('customerGroup', intval($_REQUEST['customerGroup']), $this->pluginName);
            $this->configWriter->save('paymentINVOICE', intval($_REQUEST['paymentINVOICE']), $this->pluginName);
            $this->configWriter->save('paymentCREDIT_CARD', intval($_REQUEST['paymentCREDIT_CARD']), $this->pluginName);
            $this->configWriter->save('paymentDIRECT_DEBIT', intval($_REQUEST['paymentDIRECT_DEBIT']), $this->pluginName);
            $this->configWriter->save('paymentTRANSFER', intval($_REQUEST['paymentTRANSFER']), $this->pluginName);
            $this->configWriter->save('paymentCASH_PAID', intval($_REQUEST['paymentCASH_PAID']), $this->pluginName);
            $this->configWriter->save('paymentCASH_ON_DELIVERY', intval($_REQUEST['paymentCASH_ON_DELIVERY']), $this->pluginName);
            $this->configWriter->save('paymentPAYPAL', intval($_REQUEST['paymentPAYPAL']), $this->pluginName);
            $this->configWriter->save('paymentINVOICE_TRANSFER', intval($_REQUEST['paymentINVOICE_TRANSFER']), $this->pluginName);
            $this->configWriter->save('paymentCLICKANDBUY', intval($_REQUEST['paymentCLICKANDBUY']), $this->pluginName);
            $this->configWriter->save('paymentEXPRESS_CREDITWORTHINESS', intval($_REQUEST['paymentEXPRESS_CREDITWORTHINESS']), $this->pluginName);
            $this->configWriter->save('paymentPAYNET', intval($_REQUEST['paymentPAYNET']), $this->pluginName);
            $this->configWriter->save('paymentCOD_CREDITWORTHINESS', intval($_REQUEST['paymentCOD_CREDITWORTHINESS']), $this->pluginName);
            $this->configWriter->save('paymentEBAY_EXPRESS', intval($_REQUEST['paymentEBAY_EXPRESS']), $this->pluginName);
            $this->configWriter->save('paymentMONEYBOOKERS', intval($_REQUEST['paymentMONEYBOOKERS']), $this->pluginName);
            $this->configWriter->save('paymentCREDIT_CARD_MB', intval($_REQUEST['paymentCREDIT_CARD_MB']), $this->pluginName);
            $this->configWriter->save('paymentDIRECT_DEBIT_MB', intval($_REQUEST['paymentDIRECT_DEBIT_MB']), $this->pluginName);
            $this->configWriter->save('paymentOTHERS', intval($_REQUEST['paymentOTHERS']), $this->pluginName);
        }
        catch (Exception $e) {
            $this->View()->assign([
                'success' => false,
            ]);
        }

        $this->cacheManager->clearConfigCache();
    }
}

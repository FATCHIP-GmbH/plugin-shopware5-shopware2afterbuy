<?php


class Shopware_Controllers_Backend_viaebConfigForm extends Shopware_Controllers_Backend_ExtJs
{
    /** @var \Shopware\Components\ConfigWriter $configWriter */
    protected $configWriter;

    protected $pluginName;

    /** @var \Shopware\Components\CacheManager */
    protected $cacheManager;

    public function init()
    {
        parent::init();

        $this->configWriter = Shopware()->Container()->get('config_writer');
        $this->pluginName = Shopware()->Container()->getParameter('viaeb_shopware_afterbuy.plugin_name');
        $this->cacheManager = Shopware()->Container()->get('shopware.cache_manager');
    }

    public function indexAction()
    {
    }

    public function resetAction()
    {

        $this->View()->assign([
            'success' => $result['msg'] === 'success',
            'data' => $result['data'],
            'total' => count($result['data']),
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
        }
        catch (\Exception $e) {
            $this->View()->assign([
                'success' => false,
            ]);
        }

        $this->cacheManager->clearConfigCache();
    }
}

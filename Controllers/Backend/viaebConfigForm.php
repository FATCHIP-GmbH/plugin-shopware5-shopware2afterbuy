<?php


class Shopware_Controllers_Backend_viaebConfigForm extends Shopware_Controllers_Backend_ExtJs
{
    /** @var \Shopware\Components\ConfigWriter $configWriter */
    protected $configWriter;

    protected $pluginName;

    public function init()
    {
        parent::init();

        $this->configWriter = Shopware()->Container()->get('config_writer');
        $this->pluginName = Shopware()->Container()->getParameter('viaeb_shopware_afterbuy.plugin_name');
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

    public function saveConnectionConfigAction()
    {
        $request = $this->Request();


        $this->configWriter->save('partnerId', $_REQUEST['partnerId'], $this->pluginName);
        //TODO: save

        //TODO: clear config cache
        $data = [];

        $this->View()->assign([
            'success' => true,
            'data' => $data,
            'total' => count($data),
        ]);
    }
}

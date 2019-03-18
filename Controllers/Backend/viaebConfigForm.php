<?php


class Shopware_Controllers_Backend_viaebConfigForm extends Shopware_Controllers_Backend_ExtJs
{
    /** @var \Shopware\Components\ConfigWriter $configWriter */
    protected $configWriter;

    protected $pluginName;

    public function init()
    {
        parent::init();

        $this->configWriter = $this->container->get('config_writer');
        $this->pluginName = $this->container->getParameter('');
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
        $request = $this->Request();


        $this->configWriter->save('partnerId', $_REQUEST['partnerId'], $this->pluginName);
        //TODO: save

        //TODO: clear config cache
    }
}

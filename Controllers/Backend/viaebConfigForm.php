<?php


use Exception;

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

    public function testConnectionConfigAction() {
        $testService = $this->container->get('viaeb_shopware_afterbuy.services.read_data.external.connection_test_service');
        $response = $testService->test(array(
                'partnerId' => $_REQUEST['partnerId'],
                'userName' => $_REQUEST['userName'],
                'partnerPassword' => $_REQUEST['partnerPassword'],
                'userPassword' => $_REQUEST['userPassword']
            )
        );

        if(array_key_exists('AfterbuyTimeStamp', $response['Result'])) {
            $this->view->assign([
                'success' => true,
            ]);

            return;
        }

        if(array_key_exists('ErrorList', $response['Result']) && array_key_exists('Error', $response['Result']['ErrorList'])) {

            if(array_key_exists('ErrorDescription', $response['Result']['ErrorList']['Error'])) {
                $error = $response['Result']['ErrorList']['Error']['ErrorDescription'];
            }
            else {
                $error = '';

                foreach($response['Result']['ErrorList']['Error'] as $element) {
                    $error .= $element['ErrorDescription'];
                }
            }

            $this->view->assign([
                'success' => false,
                'data' => [
                    'error' => $error,
                ],
            ]);

            return;
        }
    }

    public function getConfigValuesAction() {
        $query = Shopware()->Container()->get('dbal_connection')->createQueryBuilder();

        $query->select([
            'element.name',
            'elementValues.value as value',
        ]);

        $query->from('s_core_config_elements', 'element', 'element.name')
            ->leftJoin('element', 's_core_config_values', 'elementValues', 'elementValues.element_id = element.id AND elementValues.shop_id = :shopId')
            ->setParameter(':shopId', 1);

        $query->innerJoin('element', 's_core_config_forms', 'elementForm', 'elementForm.id = element.form_id')
            ->andWhere('elementForm.name = :namespace')
            ->setParameter(':namespace', $this->pluginName);

        $values = $query->execute()->fetchAll();

        $result = [];

        foreach($values as $value) {
            $result[$value['name']] = empty($value['value']) ? '' : unserialize($value['value']);
        }

        $this->view->assign([
            'success' => true,
            'data' => $result
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
            $this->configWriter->save('testField1', $_REQUEST['testField1'], $this->pluginName);
        }
        catch (Exception $e) {
            $this->View()->assign([
                'success' => false,
            ]);
        }

        $this->cacheManager->clearConfigCache();
    }
}

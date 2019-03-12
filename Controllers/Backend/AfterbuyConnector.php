<?php

class Shopware_Controllers_Backend_AfterbuyConnector extends Shopware_Controllers_Backend_ExtJs
{
    public function indexAction() {
    }

    public function testConnectionAction() {

        $testService = $this->container->get('viaeb_shopware_afterbuy.services.read_data.external.connection_test_service');
        $response = $testService->get(array());

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
}

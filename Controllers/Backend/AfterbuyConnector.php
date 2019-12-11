<?php
/** @noinspection SpellCheckingInspection */

/** @noinspection PhpUnused */

class Shopware_Controllers_Backend_AfterbuyConnector extends Shopware_Controllers_Backend_ExtJs
{
    public function indexAction() {
    }

    /** @noinspection PhpUnused */
    public function testConnectionAction() {

        $testService = $this->container->get('viaeb_shopware_afterbuy.services.read_data.external.connection_test_service');

        if($_REQUEST['testdata'] === 1 || $_REQUEST['testdata'] === '1') {
            $response = $testService->test(array(
                    'partnerId' => $_REQUEST['partnerId'],
                    'userName' => $_REQUEST['userName'],
                    'partnerPassword' => $_REQUEST['partnerPassword'],
                    'userPassword' => $_REQUEST['userPassword']
                )
            );
        }
        else {
            $response = $testService->get(array());
        }

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

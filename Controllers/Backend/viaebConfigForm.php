<?php


class Shopware_Controllers_Backend_viaebConfigForm extends Shopware_Controllers_Backend_ExtJs
{
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
}

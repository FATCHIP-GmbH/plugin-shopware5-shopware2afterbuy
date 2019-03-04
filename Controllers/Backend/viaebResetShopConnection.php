<?php

class Shopware_Controllers_Backend_viaebResetShopConnection extends Shopware_Controllers_Backend_ExtJs
{
    public function indexAction()
    {
    }

    public function resetAction()
    {
        $data = [];
        $errorMessage = '';
        $success = strlen($errorMessage) === 0;

        for ($i = 0; $i <= 5000000; $i++) {
            $a = 0;
        }

        $this->View()->assign([
            'success' => $success,
            'data' => $data,
            'total' => count($data),
            'errormessage' => $errorMessage,
        ]);
    }
}

<?php

use \Shopware\FatchipShopware2Afterbuy\Components\CronJob;

/**
 * Frontend controller
 */
class Shopware_Controllers_Frontend_FatchipShopware2AfterbuyTriggerCronJob extends Enlight_Controller_Action
{
    public function triggerAction() {
        Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();

        $cronjob = new CronJob();
        $response = $cronjob->importArticles2Shopware();

        var_dump($response);
        echo $response;
//        echo $this->Request()->getParam('job');
    }
}

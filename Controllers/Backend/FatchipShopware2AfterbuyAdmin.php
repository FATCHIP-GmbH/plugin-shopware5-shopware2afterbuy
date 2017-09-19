<?php

use Shopware\Components\CSRFWhitelistAware;
use Shopware\CustomModels\FatchipShopware2Afterbuy\PluginConfig;
use Shopware\FatchipShopware2Afterbuy\Components\Api\fcafterbuyapi;

/**
 * Backend controller
 */
class Shopware_Controllers_Backend_FatchipShopware2AfterbuyAdmin extends Enlight_Controller_Action implements CSRFWhitelistAware
{
    /** @var string $configModel */
    protected $configModel = 'Shopware\CustomModels\FatchipShopware2Afterbuy\PluginConfig';

    /**
     * @return string[]
     */
    public function getWhitelistedCSRFActions()
    {
        return [
            'index',
            'pluginConfig',
            'savePluginConfig',
            ];
    }

    public function indexAction()
    {
        $this->forward('pluginConfig');
    }

    public function pluginConfigAction()
    {
        $context = [
            'module' => 'pluginConfig',
            'maxHeight' => $this->View()->getAssign('maxHeight') ?: 925,
            'config' => $this->get('models')->createQueryBuilder()
                ->select('c')->from($this->configModel, 'c')->where('c.id = 1')
                ->getQuery()->execute()[0],
        ];
        $this->View()->assign($context);
    }

    public function savePluginConfigAction()
    {
        $params = $this->Request()->getParams();
        /** @var PluginConfig $config */
        $config = $this->get('models')->createQueryBuilder()
            ->select('c')->from($this->configModel, 'c')->where('c.id = 1')
            ->getQuery()->execute()[0];

        if (empty($config)) {
            $this->get('models')->createQueryBuilder()
                ->delete()->from($this->configModel, 'c')
                ->getQuery()->execute();
            $table = $this->get('models')->getClassMetadata($this->configModel)->getTableName();
            $this->get('dbal_connection')->exec("ALTER TABLE {$table} AUTO_INCREMENT = 1;");
            $config = new PluginConfig();
        }

        $config->setAfterbuyAbiUrl(trim($params['AfterbuyAbiUrl']));
        $config->setAfterbuyPartnerId(trim($params['AfterbuyPartnerId']));
        $config->setAfterbuyPartnerPassword(trim($params['AfterbuyPartnerPassword']));
        $config->setAfterbuyShopInterfaceBaseUrl(trim($params['AfterbuyShopInterfaceBaseUrl']));
        $config->setAfterbuyUsername(trim($params['AfterbuyUsername']));
        $config->setAfterbuyUserpassword(trim($params['AfterbuyUserPassword']));
        $config->setLogLevel(trim($params['LogLevel']));
        $this->get('models')->persist($config);
        $this->get('models')->flush($config);
        //Todo: Check why forward does not work

        // Todo: Test: Check Connection ot API
        $ApiClient = new fcafterbuyapi($config->toCompatArray());
        // ToDo Test: Request Afterbuy APi
        $response = $ApiClient->fcGetAfterbuyTime();
        $this->forward('pluginConfig');
    }



}

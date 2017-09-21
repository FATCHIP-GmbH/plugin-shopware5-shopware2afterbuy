<?php

use Shopware\FatchipShopware2Afterbuy\Components\CronJob;

/**
 * The Bootstrap class is the main entry point of any Shopware plugin.
 *
 * Short function reference
 * - install: Called a single time during (re)installation. Here you can trigger install-time actions like
 *   - creating the menu
 *   - creating attributes
 *   - creating database tables
 *   You need to return "true" or array('success' => true, 'invalidateCache' => array())
 *   in order to let the installation be successful
 *
 * - update: Triggered when the user updates the plugin. You will get passes the former version of the plugin as param
 *   In order to let the update be successful, return "true"
 *
 * - uninstall: Triggered when the plugin is reinstalled or uninstalled. Clean up your tables here.
 */
class Shopware_Plugins_Frontend_FatchipShopware2Afterbuy_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * Returns plugin info
     *
     * @return array
     */
    public function getInfo()
    {
        $logo = base64_encode(file_get_contents(dirname(__FILE__) . '/logo.png'));
        $info = json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'plugin.json'), true);

        $info['label'] = $info['label']['de'];
        $info['version'] = $info['currentVersion'];
        $info['description'] = '<p><img src="data:image/png;base64,' . $logo . '" /></p> '
            . file_get_contents(__DIR__ . '/README.html');

        return $info;
    }

    /**
     * Returns the current version number
     *
     * @return string
     * @throws Exception
     */
    public function getVersion()
    {
        $info = json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'plugin.json'), true);
        if ($info) {
            return $info['currentVersion'];
        } else {
            throw new Exception('The plugin has an invalid version file.');
        }
    }

    /**
     * Returns the plugin display name
     *
     * @return string
     * @throws Exception
     */
    public function getLabel()
    {
        $info = json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'plugin.json'), true);

        if ($info) {
            return $info['label']['de'];
        } else {
            throw new Exception('The plugin has an invalid version file.');
        }
    }

    /**
     * Returns the plugin solution name
     *
     * @return string
     * @throws Exception
     */
    public function getSolutionName()
    {
        $info = json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'plugin.json'), true);

        if ($info) {
            return $info['solution_name'];
        } else {
            throw new Exception('The plugin has an invalid version file.');
        }
    }

    /**
     * Returns the capabilities of the plugin
     *
     * @return array
     */
    public function getCapabilities()
    {
        return [
            'install' => true,
            'update' => true,
            'enable' => true,
            'secureUninstall' => true,
        ];
    }

    /**
     * @return array
     */
    public function enable()
    {
        return $this->invalidateCaches(true);
    }

    /**
     * @return array
     */
    public function disable()
    {
        return $this->invalidateCaches(true);
    }

    /**
     * Installs the plugin
     *
     * @return array
     */
    public function install()
    {
        $minimumVersion = $this->getInfo()['compatibility']['minimumVersion'];
        if (!$this->assertMinimumVersion($minimumVersion)) {
            throw new \RuntimeException("At least Shopware {$minimumVersion} is required");
        }

        $this->createMenuItem([
            'label' => 'Shopware2Afterbuy',
            'onclick' => 'createSimpleModule("FatchipShopware2AfterbuyAdmin", { "title": "Shopware2Afterbuy" })',
            'class' => 'icon-afterbuy',
            'active' => 1,
            'parent' => $this->Menu()->findOneBy(['controller' => 'Customer'])
        ]);

        $this->subscribeEvent('Enlight_Controller_Front_DispatchLoopStartup', 'onStartDispatch');
        $this->subscribeEvent('Shopware_CronJob_AfterBuyExport', 'onRunCronJob');
        $this->createCronJob('Afterbuy Article Export', 'AfterBuyExport', 600);

        $this->updateSchema();

        $this->createArticleAttributes();

        return ['success' => true, 'invalidateCache' => ['backend', 'config', 'proxy']];
    }

    /**
     * Uninstalls the plugin
     *
     * @return array
     */
    public function uninstall()
    {
        $this->registerCustomModels();

        $em = $this->Application()->Models();
        $tool = new \Doctrine\ORM\Tools\SchemaTool($em);
        $classes = $this->getModelClasses($em);

        $tool->dropSchema($classes);

        return $this->disable();
    }

    /**
     * Secure uninstall plugin method
     *
     * @return array
     */
    public function secureUninstall()
    {
        return $this->disable();
    }

    /**
     * Updates the plugin
     *
     * @param string $oldVersion
     * @return bool
     */
    public function update($oldVersion)
    {
        return true;
    }

    /**
     * @param bool $return
     * @return array
     */
    protected function invalidateCaches($return)
    {
        return [
            'success'         => $return,
            'invalidateCache' => [
                'backend',
                'config',
                'frontend',
                'http',
                'proxy',
                'router',
                'template',
                'theme',
            ],
        ];
    }

    /**
     * Creates the database scheme from an existing doctrine model.
     *
     * Will remove the table first, so handle with care.
     */
    protected function updateSchema()
    {
        $this->registerCustomModels();

        $em = $this->Application()->Models();
        $tool = new \Doctrine\ORM\Tools\SchemaTool($em);
        $classes = $this->getModelClasses($em);

        try {
            $tool->dropSchema($classes);
        } catch (Exception $e) {
            // ignore
        }
        $tool->createSchema($classes);
    }

    /**
     * This callback function is triggered at the very beginning of the dispatch process and allows
     * us to register additional events on the fly. This way you won't ever need to reinstall you
     * plugin for new events - any event and hook can simply be registered in the event subscribers
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onStartDispatch(Enlight_Event_EventArgs $args)
    {
        $this->registerMyComponents();
        $this->registerCustomModels();
        $this->registerMyTemplateDir();
        $this->registerMySnippets();

        $subscribers = [
            new \Shopware\FatchipShopware2Afterbuy\Subscribers\ControllerPath(),
            new \Shopware\FatchipShopware2Afterbuy\Subscribers\Backend(),
            new \Shopware\FatchipShopware2Afterbuy\Subscribers\Service(),
        ];

        foreach ($subscribers as $subscriber) {
            $this->Application()->Events()->addSubscriber($subscriber);
        }
    }

    /**
     * @param Enlight_Components_Cron_EventArgs $job
     */
    public function onRunCronJob(Enlight_Components_Cron_EventArgs $job)
    {
        $logger = $this->get('pluginlogger')->withName('hermes');
        $logger->info(date('Y-m-d H:i:s > ') . 'Hermes cronjob started.');

        $this->registerMyComponents();
        $this->registerCustomModels();
        $this->Application()->Events()->addSubscriber(
            new \Shopware\FatchipShopware2Afterbuy\Subscribers\Service()
        );

        $cronjob = new CronJob();
        $cronjob->exportArticles2Afterbuy();
    }

    /**
     * Registers the template directory, needed for templates in frontend and backend
     */
    public function registerMyTemplateDir()
    {
        Shopware()->Template()->addTemplateDir($this->Path() . 'Views');
    }

    /**
     * Registers the snippet directory, needed for backend snippets
     */
    public function registerMySnippets()
    {
        $this->Application()->Snippets()->addConfigDir(
            $this->Path() . 'Snippets/'
        );
    }

    /**
     * Registers the namespaces that are used by the plugin components
     */
    public function registerMyComponents()
    {
        $this->Application()->Loader()->registerNamespace(
            'Shopware\FatchipShopware2Afterbuy',
            $this->Path()
        );
    }

    /**
     * @param \Shopware\Components\Model\ModelManager $em
     * @return array
     */
    public function getModelClasses(\Shopware\Components\Model\ModelManager $em)
    {
        return [
            $em->getClassMetadata('Shopware\CustomModels\FatchipShopware2Afterbuy\PluginConfig'),
        ];
    }

    protected function createArticleAttributes()
    {
        $this->get('shopware_attribute.crud_service')
            ->update('s_articles_attributes', 'afterbuy_export', 'boolean', [
                // label that is going to be displayed for this attribute
                'label'            => 'Afterbuy Export',

                // user has the opportunity to translate the attribute field for each shop
                'translatable'     => false,

                // attribute will be displayed in the backend module
                'displayInBackend' => true,

                // numeric position for the backend view, sorted ascending
                'position'         => 1,

                // user can not modify the attribute in the free text field module
                'custom'           => false,
            ]);
        $this->get('models')->generateAttributeModels(['s_articles_attributes']);
    }

}

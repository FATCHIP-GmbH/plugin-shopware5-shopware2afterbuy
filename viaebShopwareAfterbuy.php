<?php /** @noinspection PhpUnhandledExceptionInspection */

namespace viaebShopwareAfterbuy;

use DateTime;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\ToolsException;
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Models\Payment\Payment;
use Shopware\Models\Payment\Repository;
use viaebShopwareAfterbuy\Models\Status;
use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Shopware\Components\Plugin\Context\UpdateContext;
use viaebShopwareAfterbuy\Services\Helper\ShopwareConfigHelper;
use Zend_Db_Adapter_Exception;
use Zend_Db_Statement_Exception;

/**
 * Shopware-Plugin FatchipAfterbuy.
 */
class viaebShopwareAfterbuy extends Plugin
{
    /**
    * @param ContainerBuilder $container
    */
    public function build(ContainerBuilder $container)
    {
        if ($container->hasParameter('kernel.default_error_level')) {
            $loglevel = $container->getParameter('kernel.default_error_level');
        } else {
            $loglevel = 100;
        }
        $container->setParameter('afterbuy.default_error_level', $loglevel);

        $container->setParameter('viaeb_shopware_afterbuy.plugin_dir', $this->getPath());
        $container->setParameter('viaeb_shopware_afterbuy.plugin_name', $this->getName());

        parent::build($container);
    }

    /**
     * @param UpdateContext $context
     */
    public function update(UpdateContext $context) {
        parent::update($context);

        if ($context->assertMinimumVersion('5.6')) {
            $this->fixDefaultValueTypes();
        }

        $this->updateAttributes();

        $this->crateFallbackPayment();
    }

    /**
     * @param InstallContext $context
     * @throws OptimisticLockException
     * @throws ToolsException
     * @throws ORMException
     */
    public function install(InstallContext $context)
    {
        parent::install($context);

        if ($context->assertMinimumVersion('5.6')) {
            $this->fixDefaultValueTypes();
        }

        $this->updateAttributes();

        /** @var EntityManager $em */
        $em = $this->container->get('models');
        $tool = new SchemaTool($em);
        $classes = [$em->getClassMetadata(Status::class)];

        $tableNames = array('afterbuy_status');

        /** @var AbstractSchemaManager $schemaManager */
        $schemaManager = Shopware()->Container()->get('models')->getConnection()->getSchemaManager();
        if (!$schemaManager->tablesExist($tableNames)) {
            $tool->createSchema($classes);

            $status = new Status();
            $status->setId(1);
            $status->setLastProductExport(new DateTime('1970-01-01'));
            $status->setLastProductImport(new DateTime('1970-01-01'));
            $status->setLastOrderImport(new DateTime('1970-01-01'));
            $status->setLastStatusExport(new DateTime('1970-01-01'));

            $em->persist($status);
            $em->flush();
        }
        $this->crateFallbackPayment();

    }

    /**
     * Since SW 5.6 config values will be expected as string. But during installation of plugin, the values will be read
     * as int from .xml file. When default values in .xml file are stored as string like in
     * &lt;value&gt;"1"&lt;/value&gt;, this value will be stored as string in db, but with double quotation marks, like
     * in 's:1:""0"";'.
     *
     * This function replaces the integer default values by string values.
     */
    public function fixDefaultValueTypes()
    {
        // Retrieve the default config setting from the configs
        // mainSystem, ExportAllArticles, ordernumberMapping
        $sql = '
                SELECT el.name, el.value
                FROM s_core_config_elements el
                LEFT JOIN s_core_config_forms form
                ON el.form_id = form.id
                WHERE (el.name="mainSystem" OR el.name="ExportAllArticles" OR el.name="ordernumberMapping") AND form.name="viaebShopwareAfterbuy"
                ';

        try {
            $stmt = Shopware()->Db()->query($sql);

            while ($row = $stmt->fetch()) {
                $fields = explode(':', rtrim($row['value'], ';'));

                // if datatype is int
                if ($fields[0] === 'i') {
                    $sql = '
                        UPDATE s_core_config_elements
                        SET value=?
                        WHERE name=?
                    ';
                    // replace int value by its string equivalent
                    // ex: 'i:0;' => 's:1:"0";'
                    Shopware()->Db()->query($sql, [
                        implode(':', ['s', sizeof($fields[1]), '"' . $fields[1] . '"']) . ';',
                        $row['name'],
                    ]);
                }
            }
        } catch (Zend_Db_Adapter_Exception $e) {
        } catch (Zend_Db_Statement_Exception $e) {
        }
    }

    public function uninstall(UninstallContext $context)
    {
        if($context->keepUserData() !== true) {
            $this->deleteAttributes();
            $this->deleteSchema();
        }
    }

    public function deleteAttributes() {
        $service = $this->container->get('shopware_attribute.crud_service');

        $service->delete('s_categories_attributes', 'afterbuy_catalog_id');
        $service->delete('s_order_attributes', 'afterbuy_order_id');
        $service->delete('s_articles_attributes', 'afterbuy_parent_id');
        $service->delete('s_articles_attributes', 'afterbuy_id');
        $service->delete('s_articles_attributes', 'afterbuy_export_enabled');
        $service->delete('s_articles_attributes', 'afterbuy_internal_number');

        Shopware()->Models()->generateAttributeModels(['s_categories_attributes', 's_order_attributes', 's_articles_attributes']);
    }

    public function deleteSchema() {
        /** @var EntityManager $em */
        $em = $this->container->get('models');
        $tool = new SchemaTool($em);
        $classes = [$em->getClassMetadata(Status::class)];

        $tableNames = array('afterbuy_status');

        $schemaManager = Shopware()->Container()->get('models')->getConnection()->getSchemaManager();
        /** @var AbstractSchemaManager $schemaManager */
        if ($schemaManager->tablesExist($tableNames)) {
            $tool->dropSchema($classes);
        }
    }

    public function updateAttributes() {
        /** @var CrudService $service */
        $service = $this->container->get('shopware_attribute.crud_service');

        $service->update('s_categories_attributes', 'afterbuy_catalog_id', 'string');

        $service->update('s_order_attributes', 'afterbuy_order_id', 'string', [
            'label' => 'Afterbuy OrderId',
            'displayInBackend' => true
        ]);

        $service->update('s_articles_attributes', 'afterbuy_parent_id', 'string');

        $service->update('s_articles_attributes', 'afterbuy_internal_number', 'string', [
            'label' => 'Afterbuy interne Artikelnummer',
            'displayInBackend' => true
        ]);

        $service->update('s_articles_attributes', 'afterbuy_id', 'string');

        $service->update('s_articles_attributes', 'afterbuy_export_enabled', 'boolean', [
            'label' => 'Artikel zu Afterbuy exportieren',
            'supportText' => 'Wenn "Alle Artikel exportieren" in den Plugineinstellungen deaktiviert ist, werden nur Artikel exportiert, für die diese Funktionalität explizit gesetzt wurde',
            'displayInBackend' => true,
        ]);

        $this->createFreeTextAttributes($service);

        Shopware()->Models()->generateAttributeModels(['s_categories_attributes', 's_order_attributes', 's_articles_attributes']);
    }

    public function createFreeTextAttributes(CrudService $service) {

        for($i = 1; $i <= 10; $i++) {
            $service->update('s_articles_attributes', 'afterbuy_free_text_' . $i, 'string', [
                'label' => 'Afterbuy Freitext ' . $i,
                'displayInBackend' => true,
                'custom' => true
            ]);
        }
    }

    public function crateFallbackPayment()
    {
        /** @var EntityManager $em */
        $em = $this->container->get('models');
        /** @var Repository $shopRepository */
        $shopRepository = $em->getRepository(Payment::class);

        $payment = new Payment();
        $payment_array = [
            'name' => ShopwareConfigHelper::$AB_UNI_PAYMENT,
            'description' => 'Afterbuy Universal',
            'additionalDescription' => 'Fallback payment for Afterbuy',
        ];

        if (!$shopRepository->findOneBy(['name' => $payment_array['name']])) {
            $payment->fromArray($payment_array);

            $em->persist($payment);
            $em->flush();
        }
    }
}

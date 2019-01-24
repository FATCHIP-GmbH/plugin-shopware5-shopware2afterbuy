<?php

namespace FatchipAfterbuy;

use FatchipAfterbuy\Models\Status;
use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\InstallContext;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Shopware-Plugin FatchipAfterbuy.
 */
class FatchipAfterbuy extends Plugin
{

    /**
    * @param ContainerBuilder $container
    */
    public function build(ContainerBuilder $container)
    {
        $container->setParameter('fatchip_afterbuy.plugin_dir', $this->getPath());
        $container->setParameter('fatchip_afterbuy.plugin_name', $this->getName());
        parent::build($container);
    }

    public function install(InstallContext $context)
    {
        parent::install($context);

        $service = $this->container->get('shopware_attribute.crud_service');
        $service->update('s_categories_attributes', 'afterbuy_catalog_id', 'string');
        $service->update('s_order_attributes', 'afterbuy_order_id', 'string');
        $service->update('s_articles_attributes', 'afterbuy_parent_id', 'string');
        $service->update('s_articles_attributes', 'afterbuy_id', 'string');

        Shopware()->Models()->generateAttributeModels(['s_categories_attributes', 's_order_attributes', 's_articles_attributes']);

        $em = $this->container->get('models');
        $tool = new \Doctrine\ORM\Tools\SchemaTool($em);
        $classes = [$em->getClassMetadata(Status::class)];

        $tableNames = array('afterbuy_status');

        $schemaManager = Shopware()->Container()->get('models')->getConnection()->getSchemaManager();
        if (!$schemaManager->tablesExist($tableNames)) {
            $tool->createSchema($classes);

            $status = new Status();
            $status->setId(1);
            $status->setLastProductExport(new \DateTime('1970-01-01'));
            $status->setLastProductImport(new \DateTime('1970-01-01'));
            $status->setLastOrderImport(new \DateTime('1970-01-01'));
            $status->setLastStatusExport(new \DateTime('1970-01-01'));

            $em->persist($status);
            $em->flush();
        }

    }

    public function afterInit()
    {



    }

}

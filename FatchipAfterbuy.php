<?php

namespace FatchipAfterbuy;

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

        Shopware()->Models()->generateAttributeModels(['s_categories_attributes']);
    }

    public function afterInit()
    {



    }

}

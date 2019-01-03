<?php

namespace FatchipAfterbuy;

use Shopware\Components\Plugin;
use Shopware\Components\Console\Application;
use FatchipAfterbuy\Commands\getCategories;
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
        parent::build($container);
    }

}

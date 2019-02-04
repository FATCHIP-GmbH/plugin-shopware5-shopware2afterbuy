<?php

namespace FatchipAfterbuy\Subscriber;

use Enlight\Event\SubscriberInterface;
use FatchipAfterbuy\Services\ReadData\External\ReadOrdersService;
use FatchipAfterbuy\Services\ReadData\ReadDataInterface;
use FatchipAfterbuy\Services\WriteData\External\WriteOrdersService;
use FatchipAfterbuy\Services\WriteData\WriteDataInterface;
use Shopware\Components\Plugin\CachedConfigReader;

class Cron implements SubscriberInterface
{
    /**
     * @var ReadDataInterface
     */
    protected $readOrderStatusService;

    /**
     * @var WriteDataInterface
     */
    protected $writeOrderStatusService;

    /**
     * @var ReadOrdersService
     */
    protected $readOrderService;

    /**
     * @var WriteOrdersService
     */
    protected $writeOrderService;

    /**
     * @var ReadDataInterface
     */
    protected $readCategoriesService;

    /**
     * @var WriteDataInterface
     */
    protected $writeCategoriesService;

    /**
     * @var ReadDataInterface
     */
    protected $readProductsService;

    /**
     * @var WriteDataInterface
     */
    protected $writeProductsService;

    public function __construct(CachedConfigReader $configReader, string $pluginName)
    {
        $config = $configReader->getByPluginName($pluginName);

        //if afterbuy data carrying system
        if($config['mainSystem'] == 2) {

        }
        //shopware is data carrying system otherwise
        else {
            $this->readCategoriesService = Shopware()->Container()->get('fatchip_afterbuy.services.read_data.internal.read_categories_service');
            $this->writeCategoriesService = Shopware()->Container()->get('fatchip_afterbuy.services.write_data.external.write_categories_service');

            $this->readProductsService = Shopware()->Container()->get('fatchip_afterbuy.services.read_data.internal.read_products_service');
            $this->writeProductsService = Shopware()->Container()->get('fatchip_afterbuy.services.write_data.external.write_products_service');

            $this->readOrderStatusService = Shopware()->Container()->get('fatchip_afterbuy.services.read_data.internal.read_status_service');
            $this->writeOrderStatusService = Shopware()->Container()->get('fatchip_afterbuy.services.write_data.external.write_status_service');

            $this->readOrderService = Shopware()->Container()->get('fatchip_afterbuy.services.read_data.external.read_orders_service');
            $this->writeOrderService = Shopware()->Container()->get('fatchip_afterbuy.services.write_data.internal.write_orders_service');
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            'Shopware_CronJob_AfterbuyUpdateProducts' => 'updateProducts',
            'Shopware_CronJob_AfterbuyUpdateOrders' => 'updateOrders',
        );
    }

    public function updateProducts(\Shopware_Components_Cron_CronJob $job)
    {
        $filter = array(
            'categories' => array(),
            'products' => array(
                'submitAll' => false
            )
        );

        $categories = $this->readCategoriesService->get($filter['categories']);
        $result = $this->writeCategoriesService->put($categories);

        $products = $this->readProductsService->get($filter['products']);
        $result = $this->writeProductsService->put($products);
    }

    public function updateOrders(\Shopware_Components_Cron_CronJob $job)
    {
        $filter = array();

        $orders = $this->readOrderStatusService->get($filter);
        $result = $this->writeOrderStatusService->put($orders);

        $filter = $this->writeOrderService->getOrderImportDateFilter(false);

        $orders = $this->readOrderService->get($filter);
        $result = $this->writeOrderService->put($orders);
    }



}

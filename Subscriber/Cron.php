<?php

namespace viaebShopwareAfterbuy\Subscriber;

use Enlight\Event\SubscriberInterface;
use Shopware_Components_Cron_CronJob;
use viaebShopwareAfterbuy\Services\ReadData\External\ReadOrdersService;
use viaebShopwareAfterbuy\Services\ReadData\ReadDataInterface;
use viaebShopwareAfterbuy\Services\WriteData\External\WriteOrdersService;
use viaebShopwareAfterbuy\Services\WriteData\WriteDataInterface;
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
            $this->readOrderService = Shopware()->Container()->get('viaeb_shopware_afterbuy.services.read_data.internal.read_orders_service');
            $this->writeOrderService = Shopware()->Container()->get('viaeb_shopware_afterbuy.services.write_data.external.write_orders_service');

            $this->readCategoriesService = Shopware()->Container()->get('viaeb_shopware_afterbuy.services.read_data.external.read_categories_service');
            $this->writeCategoriesService = Shopware()->Container()->get('viaeb_shopware_afterbuy.services.write_data.internal.write_categories_service');

            $this->readProductsService = Shopware()->Container()->get('viaeb_shopware_afterbuy.services.read_data.external.read_products_service');
            $this->writeProductsService = Shopware()->Container()->get('viaeb_shopware_afterbuy.services.write_data.internal.write_products_service');

            $this->readOrderStatusService = Shopware()->Container()->get('viaeb_shopware_afterbuy.services.read_data.external.read_orders_service');
            $this->writeOrderStatusService = Shopware()->Container()->get('viaeb_shopware_afterbuy.services.write_data.internal.write_status_service');
        }
        //shopware is data carrying system otherwise
        else {
            $this->readCategoriesService = Shopware()->Container()->get('viaeb_shopware_afterbuy.services.read_data.internal.read_categories_service');
            $this->writeCategoriesService = Shopware()->Container()->get('viaeb_shopware_afterbuy.services.write_data.external.write_categories_service');

            $this->readProductsService = Shopware()->Container()->get('viaeb_shopware_afterbuy.services.read_data.internal.read_products_service');
            $this->writeProductsService = Shopware()->Container()->get('viaeb_shopware_afterbuy.services.write_data.external.write_products_service');

            $this->readOrderStatusService = Shopware()->Container()->get('viaeb_shopware_afterbuy.services.read_data.internal.read_status_service');
            $this->writeOrderStatusService = Shopware()->Container()->get('viaeb_shopware_afterbuy.services.write_data.external.write_status_service');

            $this->readOrderService = Shopware()->Container()->get('viaeb_shopware_afterbuy.services.read_data.external.read_orders_service');
            $this->writeOrderService = Shopware()->Container()->get('viaeb_shopware_afterbuy.services.write_data.internal.write_orders_service');
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            'Shopware_CronJob_AfterbuyUpdateProducts' => 'updateProducts',
            'Shopware_CronJob_AfterbuyUpdateOrders' => 'updateOrders'
        );
    }

    /** @noinspection PhpUnused */
    public function updateProducts(
        /** @noinspection PhpUnusedParameterInspection */ Shopware_Components_Cron_CronJob $job
    )
    {
        $filter = array(
            'categories' => array(),
            'products' => array(
                'submitAll' => false
            )
        );
        $output = "";

        $categories = $this->readCategoriesService->get($filter['categories']);
        $output .= 'Got Categories: ' . count($categories). "\n";
        $this->writeCategoriesService->put($categories);

        if(method_exists($this->writeProductsService, 'getArticleImportDateFilter')) {
            $filter['products'] = $this->writeProductsService->getArticleImportDateFilter();
        }

        $products = $this->readProductsService->get($filter['products']);
        $output .= 'Got Products: ' . count($products). "\n";
        $this->writeProductsService->put($products);

        return $output;
    }

    /** @noinspection PhpUnused */
    public function updateOrders(
        /** @noinspection PhpUnusedParameterInspection */ Shopware_Components_Cron_CronJob $job
    )
    {
        $filter = array();
        $output = '';

        if(method_exists($this->writeOrderStatusService, 'getOrdersForRequestingStatusUpdate')) {
            $filter = $this->writeOrderStatusService->getOrdersForRequestingStatusUpdate();
        }

        if($this->readOrderStatusService && $this->writeOrderStatusService) {
            $orders = $this->readOrderStatusService->get($filter);
            $output .= 'Update order status: ' . count($orders) . "\n";
            $this->writeOrderStatusService->put($orders);
        }

        $filter = array();

        if(method_exists($this->writeOrderService, 'getOrderImportDateFilter')) {
            $filter = $this->writeOrderService->getOrderImportDateFilter(false);
        }

        $orders = $this->readOrderService->get($filter);
        $output .= 'Got orders: ' . count($orders). "\n";
        $this->writeOrderService->put($orders);

        return $output;
    }



}

<?php
/** @noinspection SpellCheckingInspection */

namespace viaebShopwareAfterbuy\Commands;

use viaebShopwareAfterbuy\Services\ReadData\External\ReadOrdersService;
use viaebShopwareAfterbuy\Services\ReadData\ReadDataInterface;
use viaebShopwareAfterbuy\Services\WriteData\External\WriteOrdersService;
use viaebShopwareAfterbuy\Services\WriteData\WriteDataInterface;
use Shopware\Commands\ShopwareCommand;
use Shopware\Components\Plugin\CachedConfigReader;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateOrders extends ShopwareCommand
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
     * ImportCategories constructor.
     *
     * @param CachedConfigReader $configReader
     * @param string $pluginName
     */
    public function __construct(CachedConfigReader $configReader, string $pluginName)
    {
        parent::__construct();

        $config = $configReader->getByPluginName($pluginName);

        //if afterbuy data carrying system
        if((int)$config['mainSystem'] != 2) {
            $this->readOrderStatusService = Shopware()->Container()->get('viaeb_shopware_afterbuy.services.read_data.external.read_orders_service');
            $this->writeOrderStatusService = Shopware()->Container()->get('viaeb_shopware_afterbuy.services.write_data.internal.write_status_service');

            $this->readOrderService = Shopware()->Container()->get('viaeb_shopware_afterbuy.services.read_data.internal.read_orders_service');
            $this->writeOrderService = Shopware()->Container()->get('viaeb_shopware_afterbuy.services.write_data.external.write_orders_service');
        }
        //shopware is data carrying system otherwise
        else {
            die('falscher Zweig');
            $this->readOrderStatusService = Shopware()->Container()->get('viaeb_shopware_afterbuy.services.read_data.internal.read_status_service');
            $this->writeOrderStatusService = Shopware()->Container()->get('viaeb_shopware_afterbuy.services.write_data.external.write_status_service');

            $this->readOrderService = Shopware()->Container()->get('viaeb_shopware_afterbuy.services.read_data.external.read_orders_service');
            $this->writeOrderService = Shopware()->Container()->get('viaeb_shopware_afterbuy.services.write_data.internal.write_orders_service');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('Afterbuy:Update:Orders')
            ->setDescription('Export Orders to Afterbuy')
            ->setHelp(<<<EOF
The <info>%command.name%</info> implements a command.
EOF
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * Structure for receiving and writing data
         * Should look everywhere the same.
         * Dependencies are handled via services.xml
         */

        /**
         * filter array is unused yet but can be implemented
         */
        $filter = array();

        if(method_exists($this->writeOrderStatusService, 'getOrdersForRequestingStatusUpdate')) {
            $filter = $this->writeOrderStatusService->getOrdersForRequestingStatusUpdate();
        }

        if($this->readOrderStatusService && $this->writeOrderStatusService) {
            $orders = $this->readOrderStatusService->get($filter);
            $output->writeln('Update order status: ' . count($orders));
            $this->writeOrderStatusService->put($orders);
        }

        $filter = array();

        if(method_exists($this->writeOrderService, 'getOrderImportDateFilter')) {
            $filter = $this->writeOrderService->getOrderImportDateFilter(false);
        }

        $orders = $this->readOrderService->get($filter);
        $output->writeln('Got Orders: ' . count($orders));
        $this->writeOrderService->put($orders);
    }
}

<?php
/** @noinspection SpellCheckingInspection */

namespace viaebShopwareAfterbuy\Commands;

use viaebShopwareAfterbuy\Services\ReadData\ReadDataInterface;
use viaebShopwareAfterbuy\Services\WriteData\WriteDataInterface;
use Shopware\Commands\ShopwareCommand;
use Shopware\Components\Plugin\CachedConfigReader;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateStock extends ShopwareCommand
{
    /**
     * @var ReadDataInterface
     */
    protected $readProductsService;

    /**
     * @var WriteDataInterface
     */
    protected $writeProductsService;

    protected $config;

    /**
     *
     * @param CachedConfigReader $configReader
     * @param string $pluginName
     */
    public function __construct(CachedConfigReader $configReader, string $pluginName)
    {
        parent::__construct();

        $this->config = $configReader->getByPluginName($pluginName);

        $this->readProductsService = Shopware()->Container()->get('viaeb_shopware_afterbuy.services.read_data.external.read_stock_service');
        $this->writeProductsService = Shopware()->Container()->get('viaeb_shopware_afterbuy.services.write_data.internal.write_stock_service');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('Afterbuy:Update:Stock')
            ->setDescription('Receive stock from Afterbuy')
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
        $filter = array(
            'categories' => array(),
            'products' => array(
                'submitAll' => false
            )
        );

        if((int)$this->config['mainSystem'] == 1) {
            $products = $this->readProductsService->get($filter['products']);
            $output->writeln('Got Products: ' . count($products));
            $this->writeProductsService->put($products);
        }
    }
}

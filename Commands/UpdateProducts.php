<?php

namespace FatchipAfterbuy\Commands;

use FatchipAfterbuy\Services\ReadData\ReadDataInterface;
use FatchipAfterbuy\Services\WriteData\WriteDataInterface;
use Shopware\Commands\ShopwareCommand;
use Shopware\Components\Plugin\CachedConfigReader;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateProducts extends ShopwareCommand
{
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
        if($config['mainSystem'] == 2) {
            $this->readCategoriesService = Shopware()->Container()->get('fatchip_afterbuy.services.read_data.external.read_categories_service');
            $this->writeCategoriesService = Shopware()->Container()->get('fatchip_afterbuy.services.write_data.internal.write_categories_service');

            $this->readProductsService = Shopware()->Container()->get('fatchip_afterbuy.services.read_data.external.read_products_service');
            $this->writeProductsService = Shopware()->Container()->get('fatchip_afterbuy.services.write_data.internal.write_products_service');
        }
        //shopware is data carrying system otherwise
        else {
            $this->readCategoriesService = Shopware()->Container()->get('fatchip_afterbuy.services.read_data.internal.read_categories_service');
            $this->writeCategoriesService = Shopware()->Container()->get('fatchip_afterbuy.services.write_data.external.write_categories_service');

            $this->readProductsService = Shopware()->Container()->get('fatchip_afterbuy.services.read_data.internal.read_products_service');
            $this->writeProductsService = Shopware()->Container()->get('fatchip_afterbuy.services.write_data.external.write_products_service');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('Afterbuy:Update:Products')
            ->setDescription('Export Products to Afterbuy')
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

        $categories = $this->readCategoriesService->get($filter);
        $output->writeln('Got Categories: ' . count($categories));
        $result = $this->writeCategoriesService->put($categories);

        if(method_exists($this->writeProductsService, "getArticleImportDateFilter")) {
            $filter['products'] = $this->writeProductsService->getArticleImportDateFilter();
        }

        $products = $this->readProductsService->get($filter['products']);
        $output->writeln('Got Products: ' . count($products));
        $result = $this->writeProductsService->put($products);
    }
}

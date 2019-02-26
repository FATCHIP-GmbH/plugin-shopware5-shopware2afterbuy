<?php

namespace viaebShopware2Afterbuy\Commands;

use viaebShopware2Afterbuy\Services\ReadData\ReadDataInterface;
use viaebShopware2Afterbuy\Services\WriteData\WriteDataInterface;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Shopware\Commands\ShopwareCommand;

class ImportProducts extends ShopwareCommand
{
    /**
     * @var ReadDataInterface
     */
    protected $readDataService;

    /**
     * @var WriteDataInterface
     */
    protected $writeDataService;

    /**
     * @param ReadDataInterface  $readDataService
     * @param WriteDataInterface $writeDataService
     */
    public function __construct(ReadDataInterface $readDataService, WriteDataInterface $writeDataService)
    {
        parent::__construct(null);

        $this->readDataService = $readDataService;
        $this->writeDataService = $writeDataService;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('Afterbuy:Import:Products')
            ->setDescription('Receive products from Afterbuy')
            ->addOption(
                'force',
                null,
                InputOption::VALUE_NONE,
                'Use to import all'
            )
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
         * Dependencies are handeled via services.xml
         */

        /**
         * filter array is unused yet but can be implemented
         */

        $filter = $this->writeDataService->getArticleImportDateFilter($input->getOption('force'));

        $data = $this->readDataService->get($filter);
        $output->writeln('Got ' . count($data) . ' Products');
        $this->writeDataService->put($data);
    }
}

<?php

namespace abaccAfterbuy\Commands;

use abaccAfterbuy\Services\ReadData\ReadDataInterface;
use abaccAfterbuy\Services\WriteData\Internal\WriteProductsService;
use abaccAfterbuy\Services\WriteData\WriteDataInterface;

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
     * @var WriteProductsService
     */
    protected $writeDataService;

    /**
     * @param ReadDataInterface  $readDataService
     * @param WriteDataInterface $writeDataService
     */
    public function __construct(ReadDataInterface $readDataService, WriteDataInterface $writeDataService)
    {
        parent::__construct();

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
        $filter = $this->writeDataService->getArticleImportDateFilter($input->getOption('force'));

        $data = $this->readDataService->get($filter);
        $output->writeln('Got ' . count($data) . ' Products');
        $this->writeDataService->put($data);
    }
}

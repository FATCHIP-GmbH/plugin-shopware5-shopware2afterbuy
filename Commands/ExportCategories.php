<?php
/** @noinspection SpellCheckingInspection */

namespace viaebShopwareAfterbuy\Commands;

use viaebShopwareAfterbuy\Services\ReadData\ReadDataInterface;
use viaebShopwareAfterbuy\Services\WriteData\WriteDataInterface;
use Shopware\Commands\ShopwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportCategories extends ShopwareCommand
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
     * ImportCategories constructor.
     *
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
            ->setName('Afterbuy:Export:Categories')
            ->setDescription('Export Categories to Afterbuy')
            /*->addArgument(
                'my-argument',
                InputArgument::REQUIRED,
                'An required argument (positional)'
            )
            ->addOption(
                'my-option',
                null,
                InputOption::VALUE_OPTIONAL,
                'An optional *option*',
                'My-Default-Value'
            )*/
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
        $filter = [];

        $data = $this->readDataService->get($filter);
        $output->writeln('Got ' . count($data) . ' Categgories');
        $result = $this->writeDataService->put($data);
        $output->writeln('New Categories created: ' . count($result));
    }
}

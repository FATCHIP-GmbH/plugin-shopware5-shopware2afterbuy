<?php

namespace FatchipAfterbuy\Commands;

use FatchipAfterbuy\Services\ReadData\ReadDataInterface;
use FatchipAfterbuy\Services\WriteData\WriteDataInterface;

use Symfony\Component\Console\Input\InputArgument;
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

        //TODO: set filter based on stored import date in service
        if($input->getOption('force')) {
            $filter = array();
        }
        else {
/*            $filter = array(
                'Filter' => array(
                    'FilterName' => 'DateFilter',
                    'FilterValues' => array(
                        'DateFrom' => '10.01.2019',
                        'FilterValue' => 'ModDate'
                    )
                )
            );*/

            $filter = array(
                'Filter' => array(
                    'FilterName'   => 'ProductID',
                    'FilterValues' => array(
                        'FilterValue' => array(
                            '155313626',
                            '155313627',
                            '155313628',
                            '155313629',
                            '155313630',
                            '155313631',
                            '155313632'
                        ),
                    ),
                ),
            );
        }

        $data = $this->readDataService->get($filter);
        $this->writeDataService->put($data);
    }
}

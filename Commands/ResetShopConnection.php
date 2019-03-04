<?php

namespace viaebShopwareAfterbuy\Commands;

use viaebShopwareAfterbuy\Services\Helper\ShopwareResetHelper;
use Shopware\Commands\ShopwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResetShopConnection extends ShopwareCommand
{
    /** @var ShopwareResetHelper */
    protected $shopwareResetHelper;

    public function __construct(ShopwareResetHelper $shopwareResetHelper)
    {
        parent::__construct();

        $this->shopwareResetHelper = $shopwareResetHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('Afterbuy:Reset')
            ->setDescription('Reset the connection between Shopware and Afterbuy')
            ->setHelp('The <info>%command.name%</info> resets the connection between Shopware and Afterbuy.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $result = $this->shopwareResetHelper->resetShopConnection();
        $output->writeln($result['msg']);
        foreach ($result['data'] as $entity) {
            $output->writeln($entity);
        }
    }
}

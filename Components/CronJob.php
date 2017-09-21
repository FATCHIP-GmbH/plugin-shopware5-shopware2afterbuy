<?php

namespace Shopware\FatchipShopware2Afterbuy\Components;

use Doctrine\DBAL\Connection;

/**
 * Class CronJob
 *
 * @package Shopware\FatchipShopware2Afterbuy\Components
 */
class CronJob
{
    public function exportArticles2Afterbuy()
    {
        $connection = Shopware()->Container()->get('dbal_connection');
        $client = Shopware()->Container()->get('fatchip_shopware2Afterbuy_api_client');

        return;
    }
}

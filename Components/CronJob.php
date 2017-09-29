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
        $client = Shopware()->Container()->get('fatchip_shopware2Afterbuy_api_client');

        // Get all Articles where after Attribute is set

        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(['article', 'mainDetail', 'tax', 'attribute']);
        $builder->from('Shopware\Models\Article\Article', 'article')
            ->leftJoin('article.mainDetail', 'mainDetail')
            ->leftJoin('article.tax', 'tax')
            ->leftJoin('mainDetail.attribute', 'attribute')
            ->where('attribute.afterbuyExport = 1');
        $afterbuyArticles = $builder->getQuery()->getArrayResult();

        foreach ($afterbuyArticles as $article) {
            $fcAfterbuyArt = new Api\fcafterbuyart();

            $fcAfterbuyArt->__set('UserProductID',$article['id']);  // id


        }


        return;
    }
}

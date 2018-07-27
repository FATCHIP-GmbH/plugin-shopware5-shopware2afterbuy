<?php

use \Shopware\FatchipShopware2Afterbuy\Components\ImportProductsCronJob;
use Shopware\Plugins\Community\Frontend\FatchipShopware2Afterbuy\Components\ApiClientSW;

/**
 * Frontend controller
 */
class Shopware_Controllers_Frontend_FatchipShopware2AfterbuyTriggerCronJob extends
    Enlight_Controller_Action {
    public function triggerAction() {
        Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();

        $importProductsCronJob = new ImportProductsCronJob();
        $response = $importProductsCronJob->importProducts2Shopware();

        var_dump($response);
    }

    public function callAction() {
        Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();

        $client = new ApiClientSW(
            'http://afterbuy.shop/api',
            'demo',
            'LZRULpTkI9r3JTMogaWTGLa4aR1U3oe9snhXNnZ9'
        );

        $article1 = [
            'name'       => 'testArticle',
            'taxId'      => 1,
            'mainDetail' => [
                'number' => '1',
            ],
            'supplier'   => 'testSupplier',
        ];
        $article2 = array(
            'name'             => 'Reisekoffer, in mehreren Farben',
            'description'      => 'Cognatio Inde se mos at, his Fidelis, talio mensa ops ut tam varius, at. Falx Os, iuratus in Labefacus.',
            'descriptionLong'  => ' S. Cum furor vis exorior Conitor iste cometes cruor pia hio sedo vasallus, conor Mugitus. adfero eo Bene, res facio seco dedo vix infirmus Bos nolo ausus diu farratus, palus auctor intuitus Decimus mugio terra Impleo fames Litis praetermissio. Os nec Fastidio, tot Laudunum uter lac do sed abhorreo Cognatio Inde se mos at, his Fidelis, talio mensa ops ut tam varius, at. Falx Os, iuratus in Labefacus. ',
            'shippingtime'     => '',
            'tax'              => '19',
            'keywords'         => '',
            'changed'          => '16.07.2018 12:14:07',
            'datum'            => null,
            'active'           => 1,
            'pseudosales'      => 0,
            'topseller'        => null,
            'metaTitle'        => null,
            'pricegroupID'     => null,
            'pricegroupActive' => 0,
            'filtergroupID'    => null,
            'laststock'        => '0',
            'crossbundleloock' => null,
            'notification'     => 0,
            'template'         => '',
            'mode'             => 0,
            'mainDetail'       => array(
                'number'         => '417',
                'supplierNumber' => '',
                'shippingTime'   => '',
                'laststock'      => '0',
            ),
            'variants'         => array(
                0 => array(
                    'number'         => '418',
                    'supplierNumber' => '',
                    'shippingTime'   => '',
                    'laststock'      => '0',
                ),
                1 => array(
                    'number'         => '419',
                    'supplierNumber' => '',
                    'shippingTime'   => '',
                    'laststock'      => '0',
                ),
                2 => array(
                    'number'         => '420',
                    'supplierNumber' => '',
                    'shippingTime'   => '',
                    'laststock'      => '0',
                ),
            ),
        );

        $importProductsCronJob = new ImportProductsCronJob();
        $article3
            = reset($importProductsCronJob->importProducts2Shopware(false));

        $article = $article3;

        $variants = $article['variants'];
        unset($article['variants']);

        var_dump($article);
        $result = $client->post('articles', $article);

        $articleId = json_decode($result)->data->id;

        foreach ($variants as $variant) {
            $variant['articleId'] = $articleId;
            $result = $client->post('variants', $variant);
        }

        echo $result;
        echo $articleId;
    }
}

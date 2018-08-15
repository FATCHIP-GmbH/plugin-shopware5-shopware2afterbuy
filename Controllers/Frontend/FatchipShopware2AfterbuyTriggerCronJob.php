<?php

use \Shopware\FatchipShopware2Afterbuy\Components\ImportProductsCronJob;

/**
 * Frontend controller
 */
class Shopware_Controllers_Frontend_FatchipShopware2AfterbuyTriggerCronJob extends
    Enlight_Controller_Action {
    public function triggerAction() {
        Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();

        $importProductsCronJob = new ImportProductsCronJob();
        $importProductsCronJob->importProducts2Shopware();
    }

    public function callAction() {
        Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
        Shopware()->Plugins()->Controller()->Json()->setPadding();

        $importProductsCronJob = new ImportProductsCronJob();
        $xml = $importProductsCronJob->call();

        var_dump($xml);
    }

    public function testAction() {
        $newArticle = array(
            'configuratorSet' => array(
                'groups' => array(
                    array(
                        'name' => 'Farbe',
                        'options' => array(
                            array('name' => 'Rot'),
                            array('name' => 'Blau'),
                            array('name' => 'Weiß'),
                        )
                    ),
                )
            ),
            'name'       => 'Jacke',
            'description' => 'Meta-Kurzbeschreibung',
            'descriptionLong' => '<p>asdf</p>p>',
            'shippingtime' => '',
            'taxId'      => 1,
            'keywords'         => 'Meta-Keywords',
            'changed'          => '2018-08-15 16:23:30',
            'active'           => 1,
            'pseudoSales'      => 0,
            'highlight'        => false,
            'metaTitle'        => 'Meta-Titel',
            'lastStock'        => '',
            'notification'     => false,
            'template'         => '',
            'supplier'         => '',
            'availableFrom'    => null,
            'availableTo'      => null,
            'priceGroup'       => null,
            'pricegroupActive' => false,
            'propertyGroup'    => null,
            'crossBundleLook'  => false,
            'BaseProductFlag'  => '',

            // TODO: what to map here?

            // could not find field in AB API
            'added'            => '2018-08-15',
            // not sure what kind of mode is meant
            'mode'             => 0,
            'supplierId' => 1,
            'pricegroupID' => 1,
            'variants' => array(
                array(
                    'isMain' => true,
                    'number' => 'turn',
                    'inStock' => 15,
                    'additionaltext' => 'L / Black',
                    'configuratorOptions' => array(
                        array('group' => 'Size', 'option' => 'L'),
                        array('group' => 'Color', 'option' => 'Black'),
                    ),
                    'prices' => array(
                        array(
                            'customerGroupKey' => 'EK',
                            'price' => 1999,
                        ),
                    )
                ),
                array(
                    'isMain' => false,
                    'number' => 'turn.1',
                    'inStock' => 15,
                    'additionaltext' => 'S / Black',
                    'configuratorOptions' => array(
                        array('group' => 'Size', 'option' => 'S'),
                        array('group' => 'Color', 'option' => 'Black'),
                    ),
                    'prices' => array(
                        array(
                            'customerGroupKey' => 'EK',
                            'price' => 999,
                        ),
                    )
                ),
                array(
                    'isMain' => false,
                    'number' => 'turn.2',
                    'inStock' => 15,
                    'additionaltext' => 'S / Red',
                    'configuratorOptions' => array(
                        array('group' => 'Size', 'option' => 'S'),
                        array('group' => 'Color', 'option' => 'Red'),
                    ),
                    'prices' => array(
                        array(
                            'customerGroupKey' => 'EK',
                            'price' => 999,
                        ),
                    )
                )
            )
        );

        $client->put('articles/193', $updateArticle);}
}

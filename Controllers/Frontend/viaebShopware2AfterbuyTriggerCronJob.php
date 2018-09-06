<?php

use \Shopware\viaebShopware2Afterbuy\Components\ImportProductsCronJob;

/**
 * Frontend controller
 */
class Shopware_Controllers_Frontend_viaebShopware2AfterbuyTriggerCronJob extends
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

        $parameter = array_keys($this->request->getParams())[3];
        switch ($parameter) {
            case 'products':
                $xml = $importProductsCronJob->callProducts();
                break;
            case 'catalogs':
                $xml = $importProductsCronJob->callCatalogs();
                break;
            default:
                echo 'Don\'t know what to do with parameter ' . $parameter;
        }

        var_dump($xml);
    }

    public function testAction() {
        $mainDetail = array(
            'isMain'              => true,
            'number'              => 'SW10015',
            'supplierNumber'      => '',
            'active'              => true,
            'inStock'             => '',
            'stockMin'            => '',
            'lastStock'           => '',
            'weight'              => '',
            'unit'                => '',
            'additionalText'      => '',

            // TODO: not in article model, but in db
            'sales'               => '',

            // TODO: what to map here
            'position'            => '',
            'width'               => null,
            'height'              => null,
            'len'                 => null,
            'purchaseSteps'       => '',
            'maxPurchase'         => '',
            'minPurchase'         => '',
            'purchaseUnit'        => '',
            'referenceUnit'       => '',
            'packUnit'            => '',
            'releaseDate'         => '',
            'shippingFree'        => '',
            'shippingTime'        => '',
            'purchasePrice'       => '',
            'additionaltext'      => 'Rot',
            'configuratorOptions' => array(
                array('group' => 'Farbe', 'option' => 'Rot'),
            ),
            'prices'              => array(
                array(
                    'customerGroupKey' => 'EK',
                    'price'            => 1999,
                ),
            ),
        );
        $newArticle = array(
            'configuratorSet'  => array(
                'groups' => array(
                    array(
                        'name'    => 'Farbe',
                        'options' => array(
                            array('name' => 'Rot'),
                            array('name' => 'Blau'),
                            array('name' => 'Weiß'),
                        ),
                    ),
                ),
            ),
            'name'             => 'Jacke',
            'description'      => 'Meta-Kurzbeschreibung',
            'descriptionLong'  => '<p>asdf</p>p>',
            'shippingtime'     => '',
            'taxId'            => 1,
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
            'supplierId'       => 1,
            'pricegroupID'     => 1,
            'mainDetail'       => $mainDetail,
            'variants'         => array(
                // $mainDetail,
                array(
                    'isMain'              => false,
                    'number'              => 'SW10015.1',
                    'supplierNumber'      => '',
                    'active'              => true,
                    'inStock'             => '',
                    'stockMin'            => '',
                    'lastStock'           => '',
                    'weight'              => '',
                    'unit'                => '',
                    'additionalText'      => '',

                    // TODO: not in article model, but in db
                    'sales'               => '',

                    // TODO: what to map here
                    'position'            => '',
                    'width'               => null,
                    'height'              => null,
                    'len'                 => null,
                    'purchaseSteps'       => '',
                    'maxPurchase'         => '',
                    'minPurchase'         => '',
                    'purchaseUnit'        => '',
                    'referenceUnit'       => '',
                    'packUnit'            => '',
                    'releaseDate'         => '',
                    'shippingFree'        => '',
                    'shippingTime'        => '',
                    'purchasePrice'       => '',
                    'additionaltext'      => 'Blau',
                    'configuratorOptions' => array(
                        array('group' => 'Farbe', 'option' => 'Blau'),
                    ),
                    'prices'              => array(
                        array(
                            'customerGroupKey' => 'EK',
                            'price'            => 1999,
                        ),
                    ),
                ),
                array(
                    'isMain'              => false,
                    'number'              => 'SW10015.2',
                    'supplierNumber'      => '',
                    'active'              => true,
                    'inStock'             => '',
                    'stockMin'            => '',
                    'lastStock'           => '',
                    'weight'              => '',
                    'unit'                => '',
                    'additionalText'      => '',

                    // TODO: not in article model, but in db
                    'sales'               => '',

                    // TODO: what to map here
                    'position'            => '',
                    'width'               => null,
                    'height'              => null,
                    'len'                 => null,
                    'purchaseSteps'       => '',
                    'maxPurchase'         => '',
                    'minPurchase'         => '',
                    'purchaseUnit'        => '',
                    'referenceUnit'       => '',
                    'packUnit'            => '',
                    'releaseDate'         => '',
                    'shippingFree'        => '',
                    'shippingTime'        => '',
                    'purchasePrice'       => '',
                    'additionaltext'      => 'Weiß',
                    'configuratorOptions' => array(
                        array('group' => 'Farbe', 'option' => 'Weiß'),
                    ),
                    'prices'              => array(
                        array(
                            'customerGroupKey' => 'EK',
                            'price'            => 1999,
                        ),
                    ),
                ),
            ),
        );

        $importer = new ImportProductsCronJob();
        // $importer->createArticle($newArticle);
    }
}

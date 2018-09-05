<?php

namespace  Shopware\viaebShopware2Afterbuy\Components;

/**
 * Class Helper
 *
 * @package  Shopware\viaebShopware2Afterbuy\Components
 */
class Helper
{
    /**
     * @param string $version
     * @return bool
     */
    public static function checkShopwareVersion($version = '5.3.0')
    {
        return (\Shopware::VERSION === '___VERSION___' || version_compare(\Shopware::VERSION, $version, '>='));
    }
}

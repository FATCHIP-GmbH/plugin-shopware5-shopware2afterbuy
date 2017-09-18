<?php

namespace  Shopware\FatchipShopware2Afterbuy\Components;

/**
 * Class Helper
 *
 * @package  Shopware\FatchipShopware2Afterbuy\Components
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

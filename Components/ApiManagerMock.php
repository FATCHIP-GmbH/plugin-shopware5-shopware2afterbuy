<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 02.08.18
 * Time: 13:08
 */

namespace Shopware\FatchipShopware2Afterbuy\Components;

/**
 * API Manger Mock
 */
class ApiManagerMock
{
    /**
     * @param $name
     *
     * @return \Shopware\Components\Api\Resource\Resource
     */
    public static function getResource($name)
    {
        $name = ucfirst($name);
        $class = 'Shopware\\FatchipShopware2Afterbuy\\Components\\'
            . ucfirst($name)
            . 'ResourceMock';

        /** @var $resource \Shopware\Components\Api\Resource\Resource */
        $resource = new $class();

        return $resource;
    }
}

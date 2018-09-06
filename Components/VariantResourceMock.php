<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 02.08.18
 * Time: 13:22
 */

namespace Shopware\viaebShopware2Afterbuy\Components;

use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Exception\ParameterMissingException;
use Shopware\Components\Api\Exception\ValidationException;

class VariantResourceMock {
    /**
     * @param array $article
     *
     * @throws NotFoundException
     * @throws ParameterMissingException
     * @throws ValidationException
     */
    public function create($article) {
        echo 'DBG: Variant created:<br>';

        var_dump($article);
    }

    /**
     * @param int   $id
     * @param array $article
     *
     * @throws NotFoundException
     * @throws ParameterMissingException
     * @throws ValidationException
     */
    public function update($id, $article) {
        echo 'DBG: Variant updated:<br>';
        var_dump($id);
        var_dump($article);
    }
}

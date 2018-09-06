<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 02.08.18
 * Time: 13:22
 */

namespace Shopware\FatchipShopware2Afterbuy\Components;

use Shopware\Components\Api\Exception\CustomValidationException;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Exception\ParameterMissingException;
use Shopware\Components\Api\Exception\ValidationException;

class ArticleResourceMock {
    /**
     * @param array $article
     *
     * @throws CustomValidationException
     * @throws ValidationException
     *
     * @return Article
     */
    public function create($article) {
        $articleObject = new class {
            public function getId() {
                return 1;
            }
        };
        echo 'DBG: Article created:<br>';

        var_dump($article);

        return $articleObject;
    }

    /**
     * @param int   $id
     * @param array $article
     *
     * @throws NotFoundException
     * @throws ParameterMissingException
     * @throws ValidationException
     *
     */
    public function update($id, $article) {
        echo 'DBG: Article updated:<br>';
        var_dump($id);
        var_dump($article);
    }
}

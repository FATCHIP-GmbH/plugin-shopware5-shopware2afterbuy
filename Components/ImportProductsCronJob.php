<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 16.07.18
 * Time: 10:19
 */

namespace Shopware\FatchipShopware2Afterbuy\Components;


use Doctrine\ORM\OptimisticLockException;
use Exception;
use Shopware\Components\Api\Exception\CustomValidationException;
use Shopware\Components\Api\Exception\NotFoundException;
use Shopware\Components\Api\Exception\ParameterMissingException;
use Shopware\Components\Api\Exception\ValidationException;

// TODO: remove this for productive use
// use Shopware\FatchipShopware2Afterbuy\Components\ApiMock as Api;

use Shopware\Components\Api\Resource\Article as ArticleResource;
use Shopware\Components\Api\Resource\Category as CategoryResource;

// TODO: remove this for productive use
// use Shopware\FatchipShopware2Afterbuy\Components\ArticleResourceMock as ArticleResource;

use Shopware\Components\Api\Resource\Variant as VariantResource;

// TODO: remove this for productive use
// use Shopware\FatchipShopware2Afterbuy\Components\VariantResourceMock as VariantResource;

use Shopware\Components\Api\Manager as ApiManager;

// TODO: remove this for productive use
// use Shopware\FatchipShopware2Afterbuy\Components\ApiManagerMock as ApiManager;

use Shopware\CustomModels\FatchipShopware2Afterbuy\PluginConfig;
use Shopware\Models\Article\Article;

use Shopware\Models\Article\Detail as ArticleDetail;
use Shopware\Models\Category\Category;
use Shopware\Models\Tax\Repository;
use Shopware\Models\Tax\Tax;

use Fatchip\Afterbuy\ApiClient;


/**
 * Import products from AfterBuy API and import them into shopware
 *
 * @package Shopware\FatchipShopware2Afterbuy\Components
 */
class ImportProductsCronJob {
    /** @var PluginConfig pluginConfig */
    protected $pluginConfig;

    /**
     * ImportProductsCronJob constructor.
     *
     * @param $pluginConfig
     */
    public function __construct() {
        $this->pluginConfig = Shopware()
            ->Models()
            ->getRepository(
                'Shopware\CustomModels\FatchipShopware2Afterbuy\PluginConfig'
            )
            ->findOneBy(['id' => '1']);;
    }


    /**
     * The entry point of this Class.
     */
    public function importProducts2Shopware() {
        $productsResult = $this->retrieveProductsArray();

        $products = $productsResult['Result']['Products']['Product'];

        $categoryId = $this->createCategory();

        $converter = new ProductsToArticlesConverter();
        $articles = $converter->convertProducts2Articles(
            $products,
            $categoryId
        );

        $this->importArticles($articles);

        if ($this->pluginConfig->getMissingProductsStrategy() === 'delete') {
            // TODO: delete all articles, that are in shopware, but not in $articles
        }

        var_dump($productsResult);
    }

    // TODO: remove in productive
    public function call() {
        return $this->retrieveProductsArray();
    }

    protected function createCategory() {
        /** @var CategoryResource $categoryResource */
        $categoryResource = ApiManager::getResource('category');
        $categoryName = $this->pluginConfig->getCategory();

        if ( ! $categoryName) {
            return null;
        }

        /** @var Category $category */
        $category = $categoryResource->getRepository()->findOneBy(
            ['name' => $categoryName]
        );

        if ( ! $category) {
            $newCategory = [
                'parentId' => 3,
                'name'     => $categoryName,
            ];

            try {
                $categoryResource->create($newCategory);
            } catch (ValidationException $e) {
                // TODO: handle  exception
            } catch (Exception $e) {
                // TODO: handle  exception
            }
        }

        $category = $categoryResource->getRepository()->findOneBy(
            ['name' => $categoryName]
        );

        return $category->getId();
    }

    /**
     * Call the AfterBuy API and retrieve all the products as array.
     *
     * @return array
     */
    protected function retrieveProductsArray() {
        // Get SDK object
        /** @var ApiClient $apiClient */
        $apiClient = Shopware()->Container()->get('afterbuy_api_client');
        // $apiClient = new ApiMock();


        // Get all products from AfterbuyAPI
        $productsResult = $apiClient->getShopProductsFromAfterbuy();
        if ($productsResult['CallStatus'] != 'Success') {
            // TODO: fix error handling
            die('GetShopProducts: CallStatus: ['
                . $productsResult['CallStatus']
                . '] Awaited: [Success].');
        }
        // if ($productsResult['Result']['HasMoreProducts']) {
        // pagination on
        // } else {
        // pagination off
        // }

        return $productsResult;
    }

    /**
     * Imports the given articles array into shopware.
     *
     * @param array $articles
     */
    protected function importArticles($articles) {
        $detailRepository = Shopware()->Models()->getRepository(
            'Shopware\Models\Article\Detail'
        );

        foreach ($articles as $articleArray) {

            $this->createTax($articleArray['tax']);

            // separate variantsArray from articleArray
            $variants = $articleArray['variants'];
            unset($articleArray['variants']);

            /** @var ArticleDetail $mainDetail */
            $mainDetail = $detailRepository->findOneBy(
                ['number' => $articleArray['mainDetail']['number']]
            );

            // article exists in db?
            if ($mainDetail) {
                // update it

                $articleId = $mainDetail->getArticleId();

                $this->updateArticle($articleId, $articleArray);

                foreach ($variants as $variantArray) {
                    /** @var ArticleDetail $detail */
                    $detail = $detailRepository->findOneBy(
                        ['number' => $variantArray['number']]
                    );

                    // variant exists ind db?
                    if ($detail) {
                        // update it
                        $this->updateVariant($detail->getId(), $variantArray);
                    } else {
                        // create it
                        $this->createVariant($articleId, $variantArray);
                    }
                }
            } else {
                // create it

                $articleId = $this->createArticle($articleArray);

                foreach ($variants as $variantArray) {
                    $this->createVariant($articleId, $variantArray);
                }
            }
        }
    }

    /**
     * @param $taxRate
     */
    protected function createTax($taxRate) {
        /** @var Repository $taxRepo */
        $taxRepo = Shopware()->Models()->getRepository(
            'Shopware\Models\Tax\Tax'
        );

        $tax = $taxRepo->findOneBy(['tax' => $taxRate]);

        if ( ! $tax) {
            /** @var \Shopware\Models\Tax\Tax $taxRepo */
            $tax = new Tax();

            $tax->setName($taxRate . ' %');
            $tax->setTax($taxRate);
            Shopware()->Models()->persist($tax);
            try {
                Shopware()->Models()->flush($tax);
            } catch (OptimisticLockException $e) {
            }
        }
    }

    /**
     * @param int   $articleId
     * @param array $variantArray
     */
    protected function createVariant(
        $articleId,
        $variantArray
    ) {
        /** @var VariantResource $variantResource */
        $variantResource = ApiManager::getResource('variant');

        $variantArray['articleId'] = $articleId;

        try {
            $variantResource->create($variantArray);
        } catch (NotFoundException $e) {
            // TODO: handle  exception
        } catch (ParameterMissingException $e) {
            // TODO: handle  exception
        } catch (ValidationException $e) {
            // TODO: handle  exception
        }
    }

    /**
     * @param array $articleArray
     *
     * @return null|int
     */
    protected function createArticle($articleArray) {
        /** @var ArticleResource $articleResource */
        $articleResource = ApiManager::getResource('article');

        $articleId = null;

        try {
            /** @var Article $article */
            $article = $articleResource->create($articleArray);
            $articleId = $article->getId();
        } catch (CustomValidationException $e) {
            // TODO: handle  exception
        } catch (ValidationException $e) {
            // TODO: handle  exception
        }

        return $articleId;
    }

    /**
     * @param int   $articleId
     * @param array $articleArray
     */
    protected function updateArticle(
        $articleId,
        $articleArray
    ) {
        /** @var ArticleResource $articleResource */
        $articleResource = ApiManager::getResource('article');

        try {
            $articleResource->update(
                $articleId,
                $articleArray
            );
        } catch (NotFoundException $e) {
            // TODO: handle  exception
        } catch (ParameterMissingException $e) {
            // TODO: handle  exception
        } catch (ValidationException $e) {
            // TODO: handle  exception
        }
    }

    /**
     * @param int   $variantId
     * @param array $variantArray
     */
    protected function updateVariant($variantId, $variantArray) {
        /** @var VariantResource $variantResource */
        $variantResource = ApiManager::getResource('variant');

        try {
            $variantResource->update($variantId, $variantArray);
        } catch (NotFoundException $e) {
            // TODO: handle  exception
        } catch (ParameterMissingException $e) {
            // TODO: handle  exception
        } catch (ValidationException $e) {
            // TODO: handle  exception
        }
    }
}

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

            $mappedAfterbuyArticle = $this->mapAfterbuyArticleAttributes($article);
            $resp = $client->updateArticleToAfterbuy($mappedAfterbuyArticle);
            // ToDo Update Afterbuy ProductId fpr new Articles
            // BEfore Syncing get ProductId Attribute
        }

        return true;
    }

    private function  mapAfterbuyArticleAttributes($article)
    {
        $fcAfterbuyArt = new Api\fcafterbuyart();
        $fcAfterbuyArt = $this->mapRequiredAfterbuyArticleAttributes($fcAfterbuyArt, $article);

        $fcAfterbuyArt->UserProductID           = null;
        $fcAfterbuyArt->Anr                     = $article['id'];
        $fcAfterbuyArt->EAN                     = $article['mainDetail']['number'];
        $fcAfterbuyArt->ProductID               = null;
        $fcAfterbuyArt->ShortDescription        = $article['description'];
        $fcAfterbuyArt->Memo                    = null;
        $fcAfterbuyArt->Description             = $article['descriptionLong'];
        $fcAfterbuyArt->Keywords                = $article['keywords'];
        $fcAfterbuyArt->Quantity                = $article['mainDetail']['inStock']; //????
        $fcAfterbuyArt->AuctionQuantity         = null;
        $fcAfterbuyArt->AddQuantity             = null;
        $fcAfterbuyArt->AddAuctionQuantity      = null;
        $fcAfterbuyArt->Stock                   = null;
        $fcAfterbuyArt->Discontinued            = null;
        $fcAfterbuyArt->MergeStock              = null;
        $fcAfterbuyArt->UnitOfQuantity          = null;
        $fcAfterbuyArt->BasepriceFactor         = null;
        $fcAfterbuyArt->MinimumStock            = null;
        $fcAfterbuyArt->SellingPrice            = null;
        $fcAfterbuyArt->BuyingPrice             = null;
        $fcAfterbuyArt->DealerPrice             = null;
        $fcAfterbuyArt->Level                   = null;
        $fcAfterbuyArt->Position                = null;
        $fcAfterbuyArt->TitleReplace            = null;
        $fcAfterbuyArt->ScaledQuantity          = null;
        $fcAfterbuyArt->ScaledPrice             = null;
        $fcAfterbuyArt->ScaledDPrice            = null;
        $fcAfterbuyArt->TaxRate                 = $article['tax']['tax'];
        $fcAfterbuyArt->Weight                  = $article['mainDetail']['weight'];
        $fcAfterbuyArt->Stocklocation_1         = null;
        $fcAfterbuyArt->Stocklocation_2         = null;
        $fcAfterbuyArt->Stocklocation_3         = null;
        $fcAfterbuyArt->Stocklocation_4         = null;
        $fcAfterbuyArt->CountryOfOrigin         = null;
        $fcAfterbuyArt->SearchAlias             = null;
        $fcAfterbuyArt->Froogle                 = null;
        $fcAfterbuyArt->Kelkoo                  = null;
        $fcAfterbuyArt->ShippingGroup           = null;
        $fcAfterbuyArt->ShopShippingGroup       = null;
        $fcAfterbuyArt->CrossCatalogID          = null;
        $fcAfterbuyArt->FreeValue1              = null;
        $fcAfterbuyArt->FreeValue2              = null;
        $fcAfterbuyArt->FreeValue3              = null;
        $fcAfterbuyArt->FreeValue4              = null;
        $fcAfterbuyArt->FreeValue5              = null;
        $fcAfterbuyArt->FreeValue6              = null;
        $fcAfterbuyArt->FreeValue7              = null;
        $fcAfterbuyArt->FreeValue8              = null;
        $fcAfterbuyArt->FreeValue9              = null;
        $fcAfterbuyArt->FreeValue10             = null;
        $fcAfterbuyArt->DeliveryTime            = null;
        $fcAfterbuyArt->ImageSmallURL           = null;
        $fcAfterbuyArt->ImageLargeURL           = null;
        $fcAfterbuyArt->ImageName               = null;
        $fcAfterbuyArt->ImageSource             = null;
        $fcAfterbuyArt->ManufacturerStandardProductIDType         = null;
        $fcAfterbuyArt->ManufacturerStandardProductIDValue         = null;
        $fcAfterbuyArt->ProductBrand            = null;
        $fcAfterbuyArt->CustomsTariffNumber     = null;
        $fcAfterbuyArt->ManufacturerPartNumber  = null;
        $fcAfterbuyArt->GoogleProductCategory   = null;
        $fcAfterbuyArt->Condition               = null;
        $fcAfterbuyArt->Pattern                 = null;
        $fcAfterbuyArt->Material                = null;
        $fcAfterbuyArt->ItemColor               = null;
        $fcAfterbuyArt->ItemSize                = null;
        $fcAfterbuyArt->CanonicalUrl            = null;
        $fcAfterbuyArt->EnergyClass             = null;
        $fcAfterbuyArt->EnergyClassPictureUrl   = null;
        $fcAfterbuyArt->Gender                  = null;
        $fcAfterbuyArt->AgeGroup                = null;
        $fcAfterbuyArt->ProductPicture_Nr_1     = null;
        $fcAfterbuyArt->ProductPicture_Nr_2     = null;
        $fcAfterbuyArt->ProductPicture_Nr_3     = null;
        $fcAfterbuyArt->ProductPicture_Nr_4     = null;
        $fcAfterbuyArt->ProductPicture_Nr_5     = null;
        $fcAfterbuyArt->ProductPicture_Nr_6     = null;
        $fcAfterbuyArt->ProductPicture_Nr_7     = null;
        $fcAfterbuyArt->ProductPicture_Nr_8     = null;
        $fcAfterbuyArt->ProductPicture_Nr_9     = null;
        $fcAfterbuyArt->ProductPicture_Nr_10    = null;
        $fcAfterbuyArt->ProductPicture_Nr_11    = null;
        $fcAfterbuyArt->ProductPicture_Nr_12    = null;
        $fcAfterbuyArt->ProductPicture_Url_1    = null;
        $fcAfterbuyArt->ProductPicture_Url_2    = null;
        $fcAfterbuyArt->ProductPicture_Url_3    = null;
        $fcAfterbuyArt->ProductPicture_Url_4    = null;
        $fcAfterbuyArt->ProductPicture_Url_5    = null;
        $fcAfterbuyArt->ProductPicture_Url_6    = null;
        $fcAfterbuyArt->ProductPicture_Url_7    = null;
        $fcAfterbuyArt->ProductPicture_Url_8    = null;
        $fcAfterbuyArt->ProductPicture_Url_9    = null;
        $fcAfterbuyArt->ProductPicture_Url_10   = null;
        $fcAfterbuyArt->ProductPicture_Url_11   = null;
        $fcAfterbuyArt->ProductPicture_Url_12           = null;
        $fcAfterbuyArt->ProductPicture_AltText_1        = null;
        $fcAfterbuyArt->ProductPicture_AltText_2        = null;
        $fcAfterbuyArt->ProductPicture_AltText_3        = null;
        $fcAfterbuyArt->ProductPicture_AltText_4        = null;
        $fcAfterbuyArt->ProductPicture_AltText_5        = null;
        $fcAfterbuyArt->ProductPicture_AltText_6        = null;
        $fcAfterbuyArt->ProductPicture_AltText_7        = null;
        $fcAfterbuyArt->ProductPicture_AltText_8        = null;
        $fcAfterbuyArt->ProductPicture_AltText_9        = null;
        $fcAfterbuyArt->ProductPicture_AltText_10       = null;
        $fcAfterbuyArt->ProductPicture_AltText_11       = null;
        $fcAfterbuyArt->ProductPicture_AltText_12       = null;

        return $fcAfterbuyArt;
    }

    private function  mapRequiredAfterbuyArticleAttributes($fcAfterbuyArt, $article)
    {
        $fcAfterbuyArt->Name                    = $article['name'];

        return $fcAfterbuyArt;
    }
}

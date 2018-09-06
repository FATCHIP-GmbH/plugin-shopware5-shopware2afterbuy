<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 30.08.18
 * Time: 17:41
 */

namespace Shopware\viaebShopware2Afterbuy\Components;


class CatalogsToCategoriesConverter {
    public function convertCatalogsToCategories($catalog) {
        // TODO: add field in s_categories_attributes for CatalogID

        $catId = $catalog['CatalogID'];

        $categorie = [
            'name'            => $catalog['Name'],
            'parentId'        => $catalog['ParentID'],
            'metaDescription' => $catalog['Description'],
            'position'        => $catalog['Position'],
            'active'          => $catalog['Show'],
            'media'           => $catalog['Picture1'],
        ];

        return [
            $catId => $categorie,
        ];
    }
}
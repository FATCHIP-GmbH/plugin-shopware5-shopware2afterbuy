<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 24.08.18
 * Time: 14:46
 */

namespace Shopware\Plugins\Community\Frontend\FatchipShopware2Afterbuy\Components;


class ImageCrawler {
    public function retrieveImages($products) {
        $productLinkMap = [];
        foreach ($products as $product) {
            $productId = $product['ProductID'];

            // no variation set parent?
            if ($product['BaseProductFlag'] != 1) {
                continue;
            }

            $children = $product['BaseProducts']['BaseProduct'];

            $links = [];
            foreach ($children as $child) {
                $baseProductsData = $child['BaseProductsRelationData'];
                if ($baseProductsData['DefaultProduct'] == '-1') {
                    $productId = $child['BaseProductID'];
                }

                $config = $this->processConfiguration(
                    $baseProductsData['eBayVariationData']
                );

                $link = $config['link'];

                $linkKeys = array_column($links, 'link');
                if ( ! in_array($link, $linkKeys)) {
                    $index = count($links);

                    $links[] = [
                        'link'           => $link,
                        'configurations' => [],
                    ];
                } else {
                    $index = array_search($link, $linkKeys);
                }

                $options = &$links[$index]['configurations'];
                if ( ! in_array($config['options'], $options)) {
                    $options[] = $config['options'];
                }
            }

            $productLinkMap[$productId] = $links;
        }

        return $productLinkMap;
    }

    /**
     * @param array $configuration
     *
     * @return array
     */
    protected function processConfiguration($configuration) {
        $result = [];

        // multiple options?
        if (array_key_exists(0, $configuration)) {
            foreach ($configuration as $option) {
                $result = $this->addimageToArray($option);
            }
        } else {
            $result = $this->addimageToArray($configuration);
        }

        return $result;
    }

    /**
     * @param $option
     *
     * @return mixed
     */
    protected function addimageToArray($option) {
        $result = [];

        $imageLink = $option['eBayVariationUrls'];

        if ($imageLink) {
            $result['link'] = $imageLink;
            $result['options'][] = $option['eBayVariationValue'];
        }

        return $result;
}
}

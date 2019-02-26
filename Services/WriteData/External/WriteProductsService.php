<?php

namespace viaebShopwareAfterBuy\Services\WriteData\External;

use Fatchip\Afterbuy\ApiClient;
use viaebShopwareAfterBuy\Components\Helper;
use viaebShopwareAfterBuy\Services\Helper\AfterbuyProductsHelper;
use viaebShopwareAfterBuy\Services\Helper\ShopwareArticleHelper;
use viaebShopwareAfterBuy\Services\WriteData\AbstractWriteDataService;
use viaebShopwareAfterBuy\Services\WriteData\WriteDataInterface;
use viaebShopwareAfterBuy\ValueObjects\Article;
use Shopware\Models\Article\Detail;
use Shopware\Models\Customer\Group;


class WriteProductsService extends AbstractWriteDataService implements WriteDataInterface {

    /**
     * @param array $data
     * @return mixed|void
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function put(array $data) {
        $data = $this->transform($data);
        return $this->send($data);
    }

    /**
     * transforms valueObject into final structure for storage
     * could may be moved into separate helper
     *
     * @param array $data
     * @return array
     */
    public function transform(array $data) {
        $this->logger->debug("Storing " . count($data) . " items.", array($data));
        /**
         * @var Group $customerGroup
         */

        $api = new ApiClient($this->apiConfig, $this->logger);

        /**
         * @var AfterbuyProductsHelper $helper
         */
        $helper = $this->helper;

        $afterbuyProductIds = $helper->submitAfterbuySimpleProducts($data, $api);

        $afterbuyProductIds = $helper->submitAfterbuyVariantProducts($data, $api, $afterbuyProductIds);

        return $afterbuyProductIds;
    }


    /**
     * @param $targetData
     * @return mixed     *
     */
    public function send($targetData) {

        $this->helper->updateExternalIds($targetData);

        $this->storeSubmissionDate('lastProductExport');

        if(!is_array($targetData)) {
            $targetData = array();
        }

        return $targetData;
    }
}
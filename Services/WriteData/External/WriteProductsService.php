<?php

namespace FatchipAfterbuy\Services\WriteData\External;

use Fatchip\Afterbuy\ApiClient;
use FatchipAfterbuy\Components\Helper;
use FatchipAfterbuy\Services\Helper\AfterbuyProductsHelper;
use FatchipAfterbuy\Services\Helper\ShopwareArticleHelper;
use FatchipAfterbuy\Services\WriteData\AbstractWriteDataService;
use FatchipAfterbuy\Services\WriteData\WriteDataInterface;
use FatchipAfterbuy\ValueObjects\Article;
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
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function transform(array $data) {
        /**
         * @var Group $customerGroup
         */

        $api = new ApiClient($this->apiConfig);

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
     * @return mixed|void
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function send($targetData) {

        $this->helper->updateExternalIds($targetData);

        $this->storeSubmissionDate('lastProductExport');

        return $targetData;
    }
}
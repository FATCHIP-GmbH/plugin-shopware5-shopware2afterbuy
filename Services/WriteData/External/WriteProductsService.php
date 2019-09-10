<?php
/** @noinspection SpellCheckingInspection */

namespace viaebShopwareAfterbuy\Services\WriteData\External;

use Fatchip\Afterbuy\ApiClient;
use viaebShopwareAfterbuy\Services\Helper\AfterbuyProductsHelper;
use viaebShopwareAfterbuy\Services\WriteData\AbstractWriteDataService;
use viaebShopwareAfterbuy\Services\WriteData\WriteDataInterface;
use Shopware\Models\Customer\Group;


class WriteProductsService extends AbstractWriteDataService implements WriteDataInterface {
    /** @var AfterbuyProductsHelper */
    public $helper;

    /**
     * @param null|array $data
     * @return mixed
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
        $this->logger->debug('Storing ' . count($data) . ' items.', array($data));
        /**
         * @var Group $customerGroup
         */

        $api = new ApiClient($this->apiConfig, $this->logger);

        /**
         * @var AfterbuyProductsHelper $helper
         */
        $helper = $this->helper;

        //TODO: move to send method
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
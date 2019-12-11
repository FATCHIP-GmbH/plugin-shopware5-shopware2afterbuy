<?php
/** @noinspection SpellCheckingInspection */

namespace viaebShopwareAfterbuy\Services\WriteData\External;

use Fatchip\Afterbuy\ApiClient;
use viaebShopwareAfterbuy\Services\Helper\AfterbuyProductsHelper;
use viaebShopwareAfterbuy\Services\WriteData\AbstractWriteDataService;
use viaebShopwareAfterbuy\Services\WriteData\WriteDataInterface;

/**
 * Class WriteProductsService
 * @package viaebShopwareAfterbuy\Services\WriteData\External
 * @property AfterbuyProductsHelper $helper
 */
class WriteProductsService extends AbstractWriteDataService implements WriteDataInterface {
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

        $api = new ApiClient($this->apiConfig, $this->logger);

        $afterbuyProductIds = $this->helper->submitAfterbuySimpleProducts($data, $api);

        $afterbuyProductIds = $this->helper->submitAfterbuyVariantProducts($data, $api, $afterbuyProductIds);

        return $afterbuyProductIds;
    }

    /**
     * @param $targetData
     * @return mixed
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
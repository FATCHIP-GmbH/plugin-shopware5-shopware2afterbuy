<?php

namespace viaebShopwareAfterBuy\Services\WriteData\External;

use Fatchip\Afterbuy\ApiClient;
use viaebShopwareAfterBuy\Services\Helper\ShopwareCategoryHelper;
use viaebShopwareAfterBuy\Services\WriteData\AbstractWriteDataService;
use viaebShopwareAfterBuy\Services\WriteData\WriteDataInterface;
use viaebShopwareAfterBuy\ValueObjects\Category as ValueCategory;

class WriteCategoriesService extends AbstractWriteDataService implements WriteDataInterface
{

    /**
     * @var string $identifier
     */
    protected $identifier;

    /**
     * @var bool $isAttribute
     */
    protected $isAttribute;

    /** @var ShopwareCategoryHelper $helper */
    public $helper;

    /**
     * @param ValueCategory[] $valueCategories
     *
     * @return array
     */
    public function put(array $valueCategories)
    {
        $catalogs = $this->transform($valueCategories);

        return $this->send($catalogs);
    }

    /**
     * transforms valueObject into final structure for storage
     * could may be moved into separate helper
     *
     * @param ValueCategory[] $valueCategories
     *
     * @return array
     */
    public function transform(array $valueCategories)
    {
        $this->logger->debug('Got ' . count($valueCategories) . ' items', [$valueCategories]);
        return $this->helper->buildAfterbuyCatalogStructure($valueCategories);
    }

    /**
     * @param [] $catalogs
     *
     * @return array
     */
    public function send($catalogs)
    {
        /** @var ApiClient $api */
        $api = new ApiClient($this->apiConfig, $this->logger);

        $response = $api->updateCatalogs($catalogs);

        $catalogIds = $this->helper->getCatalogIdsFromResponse($response);

        try {
            $this->helper->updateExternalIds($catalogIds);
        }
        catch(\Exception $e) {
            $this->logger->error('Could not store external category ids');
        }


        return $catalogIds;
    }
}

<?php

namespace FatchipAfterbuy\Services\WriteData\External;

use Fatchip\Afterbuy\ApiClient;
use FatchipAfterbuy\Services\Helper\ShopwareCategoryHelper;
use FatchipAfterbuy\Services\WriteData\AbstractWriteDataService;
use FatchipAfterbuy\Services\WriteData\WriteDataInterface;
use FatchipAfterbuy\ValueObjects\Category as ValueCategory;

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

    /**
     * @param ValueCategory[] $valueCategories
     *
     * @return string
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
    public function transform(array $valueCategories): array
    {
        $this->logger->info('Got ' . count($valueCategories) . ' items', ['Categories', 'Write', 'External']);

        /** @var ShopwareCategoryHelper $helper */
        $helper = $this->helper;

        return $helper->buildAfterbuyCatalogStructure($valueCategories);
    }

    /**
     * @param [] $catalogs
     *
     * @return string
     */
    public function send($catalogs)
    {
        /** @var ApiClient $api */
        $api = new ApiClient($this->apiConfig);

        $response = $api->updateCatalogs($catalogs);

        $catalogIds = $this->helper->getCatalogIdsFromResponse($response);
        $this->helper->updateExternalIds($catalogIds);

        //TODO: set external identifier if available
        //TODO: test nur auf unterster ebene
    }


}

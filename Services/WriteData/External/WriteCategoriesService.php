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
    public function transform(array $valueCategories): array
    {
        $this->logger->info('Got ' . count($valueCategories) . ' items', ['Categories', 'Write', 'External']);
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
        $api = new ApiClient($this->apiConfig);

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

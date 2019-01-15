<?php

namespace FatchipAfterbuy\Services\WriteData\External;

use Fatchip\Afterbuy\ApiClient;
use FatchipAfterbuy\Services\Helper\AbstractHelper;
use FatchipAfterbuy\Services\Helper\ShopwareCategoryHelper;
use FatchipAfterbuy\Services\WriteData\AbstractWriteDataService;
use FatchipAfterbuy\Services\WriteData\WriteDataInterface;
use FatchipAfterbuy\ValueObjects\Category as ValueCategory;

class WriteCategoriesService extends AbstractWriteDataService implements WriteDataInterface
{

    /**
     * @var ShopwareCategoryHelper $categoryHelper
     */
    protected $categoryHelper;

    /**
     * @var string $identifier
     */
    protected $identifier;

    /**
     * @var bool $isAttribute
     */
    protected $isAttribute;

    /**
     * @param AbstractHelper $helper
     * @param string         $identifier
     * @param bool           $isAttribute
     */
    public function initHelper(AbstractHelper $helper, string $identifier, bool $isAttribute)
    {
        $this->categoryHelper = $helper;
        $this->identifier = $identifier;
        $this->isAttribute = $isAttribute;
    }

    /**
     * @param ValueCategory[] $valueCategories
     *
     * @return string
     */
    public function put(array $valueCategories): string
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

        return $this->categoryHelper->buildAfterbuyCatalogStructure($valueCategories);
    }

    /**
     * @param [] $catalogs
     *
     * @return string
     */
    public function send($catalogs): string
    {
        /** @var ApiClient $api */
        $api = new ApiClient($this->apiConfig);

        return $api->updateCatalogs($catalogs);
    }
}

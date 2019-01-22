<?php

namespace FatchipAfterbuy\Services\WriteData\External;

use Fatchip\Afterbuy\ApiClient;
use FatchipAfterbuy\Services\Helper\ShopwareCategoryHelper;
use FatchipAfterbuy\Services\WriteData\AbstractWriteDataService;
use FatchipAfterbuy\Services\WriteData\WriteDataInterface;
use FatchipAfterbuy\ValueObjects\Category as ValueCategory;

class WriteStatusService extends AbstractWriteDataService implements WriteDataInterface
{

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

    }
}

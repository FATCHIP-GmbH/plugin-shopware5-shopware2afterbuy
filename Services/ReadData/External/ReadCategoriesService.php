<?php

namespace FatchipAfterbuy\Services\ReadData\External;

use Fatchip\Afterbuy\ApiClient;
use FatchipAfterbuy\Services\ReadData\AbstractReadDataService;
use FatchipAfterbuy\Services\ReadData\ReadDataInterface;
use FatchipAfterbuy\ValueObjects\Category;

class ReadCategoriesService extends AbstractReadDataService implements ReadDataInterface
{

    /**
     * @param array $filter
     *
     * @return array|null
     */
    public function get(array $filter)
    {
        $data = $this->read($filter);

        return $this->transform($data);
    }

    /**
     * transforms api input into valueObject (targetEntity)
     *
     * @param array $data
     *
     * @return array|null
     */
    public function transform(array $data)
    {
        if ($this->targetEntity === null) {

            $this->logger->error('No target entity defined!', array('Categories', 'Read', 'External'));

            return null;
        }

        $this->logger->info('Got ' . count($data) . ' items', array('Categories', 'Read', 'External'));

        $targetData = array();

        foreach ($data as $entity) {

            /**
             * @var Category $value
             */
            $value = new $this->targetEntity();

            //mappings for valueObject
            $fieldMappings = [
                ['CatalogID', 'ExternalIdentifier'],
                ['Name', 'Name'],
                ['Description', 'Description'],
                ['ParentID', 'ParentIdentifier'],
                ['Position', 'Position'],
                ['AdditionalText', 'CmsText'],
                ['Show', 'Active'],
                ['Picture1', 'Image'],
            ];

            foreach ($fieldMappings as [$afterbuyVar, $valueObjVar]) {
                if (isset($entity[$afterbuyVar])) {
                    $setter = 'set' . $valueObjVar;
                    $value->$setter($entity[$afterbuyVar]);
                }
            }

            if ($value->isValid()) {
                $targetData[] = $value;
            } else {
                // TODO: log error message
                // throw new RuntimeException('value is not valid');
            }
        }

        return $targetData;
    }


    /**
     * provides api data. dummy data as used here can be used in tests
     *
     * @param array $filter
     *
     * @return array
     */
    public function read(array $filter): array
    {
        // $pageNumber = 0;

        /** @var ApiClient $api */
        $api = new ApiClient($this->apiConfig);

        // do {
        $catalogsResult = $api->getCatalogsFromAfterbuy(200, 2, 0);
        $catalogs = $catalogsResult['Result']['Catalogs']['Catalog'];
        // } while ($catalogsResult['Result']['HasMoreCatalogs']);

        if ( ! $catalogs) {
            $this->logger->error('No data received', array('Categories', 'Read', 'External'));
        }

        return $catalogs;
    }
}
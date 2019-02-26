<?php

namespace viaebShopwareAfterBuy\Services\ReadData\External;

use Fatchip\Afterbuy\ApiClient;
use viaebShopwareAfterBuy\Services\ReadData\AbstractReadDataService;
use viaebShopwareAfterBuy\Services\ReadData\ReadDataInterface;
use viaebShopwareAfterBuy\ValueObjects\Category;

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
        $this->logger->debug('Receiving categories from afterbuy', $data);

        if ($this->targetEntity === null) {

            $this->logger->error('No target entity defined!', array('Categories', 'Read', 'External'));

            return null;
        }

        $this->logger->info('Got ' . count($data) . ' items', array('Categories', 'Read', 'External'));

        $targetData = array();

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

        if ($data && ! is_array($data[0])) {
            $data = array($data);
        }

        foreach ($data as $entity) {
            /**
             * @var Category $value
             */
            $value = new $this->targetEntity();

            foreach ($fieldMappings as list($afterbuyVar, $valueObjVar)) {
                if (isset($entity[$afterbuyVar])) {
                    $setter = 'set' . $valueObjVar;
                    $value->$setter($entity[$afterbuyVar]);
                }
            }

            if ($value->isValid()) {
                $targetData[] = $value;
            } else {
                $this->logger->error('Error submitting category', array($entity));
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
    public function read(array $filter)
    {
        /** @var ApiClient $api */
        $api = new ApiClient($this->apiConfig, $this->logger);

        // do {
        $catalogsResult = $api->getCatalogsFromAfterbuy(200, 2, 0, $filter);
        $catalogs = $catalogsResult['Result']['Catalogs']['Catalog'];

        if ( ! $catalogs) {
            $this->logger->error('No data received', array('Categories', 'Read', 'External'));
        }

        if($catalogs === null) {
            $catalogs = [];
        }

        return $catalogs;
    }
}

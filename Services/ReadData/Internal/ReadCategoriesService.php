<?php

namespace FatchipAfterbuy\Services\ReadData\Internal;

use FatchipAfterbuy\Services\ReadData\AbstractReadDataService;
use FatchipAfterbuy\Services\ReadData\ReadDataInterface;

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
        return $data;
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
        $data = [];

        return $data;
    }
}

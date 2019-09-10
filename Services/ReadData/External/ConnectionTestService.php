<?php
/** @noinspection SpellCheckingInspection */

namespace viaebShopwareAfterbuy\Services\ReadData\External;

use Fatchip\Afterbuy\ApiClient;
use viaebShopwareAfterbuy\Services\ReadData\AbstractReadDataService;
use viaebShopwareAfterbuy\Services\ReadData\ReadDataInterface;


class ConnectionTestService extends AbstractReadDataService implements ReadDataInterface
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
    public function read(array $filter)
    {
        /** @var ApiClient $api */
        $api = new ApiClient($this->apiConfig);
        return $api->getAfterbuyTime();
    }

    public function test(array $config) {
        $apiTestConfig = [
            'afterbuyAbiUrl'               => 'https://api.afterbuy.de/afterbuy/ABInterface.aspx',
            'afterbuyShopInterfaceBaseUrl' => 'https://api.afterbuy.de/afterbuy/ShopInterfaceUTF8.aspx',
            'afterbuyPartnerId'            => $config['partnerId'],
            'afterbuyPartnerPassword'      => $config['partnerPassword'],
            'afterbuyUsername'             => $config['userName'],
            'afterbuyUserPassword'         => $config['userPassword'],
            'logLevel'                     => '1',
        ];

        $api = new ApiClient($apiTestConfig);
        return $api->getAfterbuyTime();
    }
}

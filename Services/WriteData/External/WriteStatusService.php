<?php

namespace FatchipAfterbuy\Services\WriteData\External;

use Fatchip\Afterbuy\ApiClient;
use FatchipAfterbuy\Components\Helper;
use FatchipAfterbuy\Services\Helper\ShopwareCategoryHelper;
use FatchipAfterbuy\Services\WriteData\AbstractWriteDataService;
use FatchipAfterbuy\Services\WriteData\WriteDataInterface;
use FatchipAfterbuy\ValueObjects\Category as ValueCategory;
use FatchipAfterbuy\ValueObjects\OrderStatus;

class WriteStatusService extends AbstractWriteDataService implements WriteDataInterface
{

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

    public function transform(array $orders): array
    {
        if(empty($orders)) {
            return array();
        }

        $content = array(
            'Orders' => array()
        );

        foreach($orders as $order) {
            /**
             * @var OrderStatus $order
             */

            $content['Orders'][] = array(
                'Order' => array(
                    'OrderID' => $order->getAfterbuyOrderId(),
                    'PaymentInfo' => array(
                        'PaymentDate' => date_format($order->getPaymentDate(), 'd.m.Y H:i:s'),
                    ),
                    'ShippingInfo' => array(
                        'DeliveryDate' => date_format($order->getShippingDate(), 'd.m.Y H:i:s')
                    )
                )
            );
        }

        return $content;
    }

    /**
     * @param [] $catalogs
     *
     * @return string
     */
    public function send($orders): ?string
    {
        /** @var ApiClient $api */
        $api = new ApiClient($this->apiConfig);
        $api->updateOrderStatus($orders);

        return null;

    }
}

<?php

namespace viaebShopware2Afterbuy\Services\WriteData\External;

use Fatchip\Afterbuy\ApiClient;
use viaebShopware2Afterbuy\Components\Helper;
use viaebShopware2Afterbuy\Services\Helper\ShopwareCategoryHelper;
use viaebShopware2Afterbuy\Services\WriteData\AbstractWriteDataService;
use viaebShopware2Afterbuy\Services\WriteData\WriteDataInterface;
use viaebShopware2Afterbuy\ValueObjects\Category as ValueCategory;
use viaebShopware2Afterbuy\ValueObjects\OrderStatus;

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

    public function transform(array $orders)
    {
        $this->logger->debug("Storing " . count($orders) . " items.", array($orders));

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
                        'AlreadyPaid' => Helper::convertNumberToABString($order->getAmount())
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
     * @return array
     */
    public function send($orders)
    {
        /** @var ApiClient $api */
        $api = new ApiClient($this->apiConfig, $this->logger);

        if(is_array($orders) && count($orders)) {
            $response = $api->updateOrderStatus($orders);

            if ($response['CallStatus'] === 'Error') {
                $this->logger->error('Error submitting data', $response);
            }
        }

        $this->storeSubmissionDate('lastStatusExport');

        return array();
    }
}

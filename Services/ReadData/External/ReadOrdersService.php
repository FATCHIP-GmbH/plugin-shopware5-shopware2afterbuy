<?php

namespace FatchipAfterbuy\Services\ReadData\External;

use Fatchip\Afterbuy\ApiClient;
use FatchipAfterbuy\Services\ReadData\AbstractReadDataService;
use FatchipAfterbuy\Services\ReadData\ReadDataInterface;
use FatchipAfterbuy\ValueObjects\Address;
use FatchipAfterbuy\ValueObjects\Order;

class ReadOrdersService extends AbstractReadDataService implements ReadDataInterface {

    /**
     * @param array $filter
     * @return array|null
     */
    public function get(array $filter) {
        $data = $this->read($filter);
        return $this->transform($data);
    }

    /**
     * transforms api input into valueObject (targetEntity)
     *
     * @param array $data
     * @return array|null
     */
    public function transform(array $data) {
        if($this->targetEntity === null) {
            return null;
        }

        $targetData = array();

        foreach($data["Result"]["Orders"]["Order"] as $entity) {

            /**
             * @var Order $value
             */
            $value = new $this->targetEntity();

            //mappings for valueObject
            $value->setExternalIdentifier($entity["OrderID"]);
            $value->setAmount($entity["PaymentInfo"]["FullAmount"]);

            //Status
            //TODO: set status

            //Positions
            //TODO: set positions

            //Addresses
            $billingAddress = new Address();

            $billingAddress->setFirstname($entity["BuyerInfo"]["BillingAddress"]["FirstName"]);
            $billingAddress->setLastname($entity["BuyerInfo"]["BillingAddress"]["LastName"]);

            if($entity["BuyerInfo"]["BillingAddress"]["Title"] == "Frau") {
                $billingAddress->getSalutation('mrs');
            } else {
                $billingAddress->getSalutation('mr');
            }

            $billingAddress->setCompany($entity["BuyerInfo"]["BillingAddress"]["Company"]);
            $billingAddress->setStreet($entity["BuyerInfo"]["BillingAddress"]["Street"]);
            $billingAddress->setAdditionalAddressLine1($entity["BuyerInfo"]["BillingAddress"]["Street2"]);
            $billingAddress->setZipcode($entity["BuyerInfo"]["BillingAddress"]["PostalCode"]);
            $billingAddress->setCity($entity["BuyerInfo"]["BillingAddress"]["City"]);
            $billingAddress->setCountry($entity["BuyerInfo"]["BillingAddress"]["CountryISO"]);
            $billingAddress->setPhone($entity["BuyerInfo"]["BillingAddress"]["Phone"]);
            $billingAddress->setVatId($entity["BuyerInfo"]["BillingAddress"]["TaxIDNumber"]);
            $billingAddress->setEmail($entity["BuyerInfo"]["BillingAddress"]["Mail"]);

            $value->setBillingAddress($billingAddress);

            if($entity["BuyerInfo"]["ShippingAddress"]) {
                $shippingAddress = new Address();

                $shippingAddress->setFirstname($entity["BuyerInfo"]["ShippingAddress"]["FirstName"]);
                $shippingAddress->setLastname($entity["BuyerInfo"]["ShippingAddress"]["LastName"]);

                if($entity["BuyerInfo"]["ShippingAddress"]["Title"] == "Frau") {
                    $shippingAddress->getSalutation('mrs');
                } else {
                    $shippingAddress->getSalutation('mr');
                }

                $shippingAddress->setCompany($entity["BuyerInfo"]["ShippingAddress"]["Company"]);
                $shippingAddress->setStreet($entity["BuyerInfo"]["ShippingAddress"]["Street"]);
                $shippingAddress->setAdditionalAddressLine1($entity["BuyerInfo"]["ShippingAddress"]["Street2"]);
                $shippingAddress->setZipcode($entity["BuyerInfo"]["ShippingAddress"]["PostalCode"]);
                $shippingAddress->setCity($entity["BuyerInfo"]["ShippingAddress"]["City"]);
                $shippingAddress->setCountry($entity["BuyerInfo"]["ShippingAddress"]["CountryISO"]);
                $shippingAddress->setPhone($entity["BuyerInfo"]["ShippingAddress"]["Phone"]);
                $shippingAddress->setVatId($entity["BuyerInfo"]["ShippingAddress"]["TaxIDNumber"]);
                $shippingAddress->setEmail($entity["BuyerInfo"]["ShippingAddress"]["Mail"]);

                $value->setShippingAddress($billingAddress);
            }



            array_push($targetData, $value);
        }

        return $targetData;
    }


    /**
     * provides api data. dummy data as used here can be used in tests
     *
     * @param array $filter
     * @return array
     */
    public function read(array $filter) {

        $resource = new ApiClient($this->apiConfig);
        $data = $resource->getOrdersFromAfterbuy();

        if(!$data || !$data["Result"]) {
            return null;
        }

        return $data;
    }
}
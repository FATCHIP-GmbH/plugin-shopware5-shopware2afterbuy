<?php

namespace FatchipAfterbuy\Services\ReadData\External;

use Fatchip\Afterbuy\ApiClient;
use FatchipAfterbuy\Components\Helper;
use FatchipAfterbuy\Services\ReadData\AbstractReadDataService;
use FatchipAfterbuy\Services\ReadData\ReadDataInterface;
use FatchipAfterbuy\ValueObjects\Address;
use FatchipAfterbuy\ValueObjects\Order;
use FatchipAfterbuy\ValueObjects\OrderPosition;

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
            $value->setAmount(Helper::convertDeString2Float($entity["PaymentInfo"]["FullAmount"]));

            //Status
            //TODO: set status

            //Positions
            /**
             * structure differs is multiple articles per order / need to handle
             */

            $netAmount = 0;

            if(intval($entity["SoldItems"]["ItemsInOrder"]) > 1) {
                foreach($entity["SoldItems"]["SoldItem"] as $position) {
                    $orderPosition = new OrderPosition();

                    $orderPosition->setName($position["ItemTitle"]);
                    $orderPosition->setExternalIdentifier($position["ShopProductDetails"]["ProductID"]);
                    $orderPosition->setQuantity($position["ItemQuantity"]);
                    $orderPosition->setPrice(Helper::convertDeString2Float($position["ItemPrice"]));
                    $orderPosition->setTax(Helper::convertDeString2Float($position["TaxRate"]));

                    $value->getPositions()->add($orderPosition);

                    //TODO: fix
                    $netAmount += $position["ItemQuantity"] * (Helper::convertDeString2Float($position["ItemPrice"]) / (1 + Helper::convertDeString2Float($position["TaxRate"]) / 100));
                }
            } else {
                $orderPosition = new OrderPosition();

                $orderPosition->setName($entity["SoldItems"]["SoldItem"]["ItemTitle"]);
                $orderPosition->setPrice(Helper::convertDeString2Float($entity["SoldItems"]["SoldItem"]["ItemPrice"]));
                $orderPosition->setExternalIdentifier($entity["SoldItems"]["SoldItem"]["ShopProductDetails"]["ProductID"]);
                $orderPosition->setQuantity($entity["SoldItems"]["SoldItem"]["ItemQuantity"]);
                $orderPosition->setTax(Helper::convertDeString2Float($entity["SoldItems"]["SoldItem"]["TaxRate"]));

                $value->getPositions()->add($orderPosition);

                $netAmount += Helper::convertDeString2Float($entity["SoldItems"]["SoldItem"]["ItemPrice"]) / (1 + Helper::convertDeString2Float($entity["SoldItems"]["SoldItem"]["TaxRate"]) / 100);
            }





            //Shipping

            //Payment

/*
            ...PaymentInfo.PaymentID
            INVOICE - Vorkasse/Überweisung
            CREDIT_CARD - Kreditkarte
            DIRECT_DEBIT - Bankeinzug

            ...PaymentInfo.PaymentFunction
            TRANSFER - Überweisung
            CASH_PAID - Bar/Abholung
            CASH_ON_DELIVERY - Nachnahme
            PAYPAL - PayPal
            INVOICE_TRANSFER - Überweisung/Rechnung
            DIRECT_DEBIT - Bankeinzug
            CLICKANDBUY - ClickAndBuy
            EXPRESS_CREDITWORTHINESS - Expresskauf/Bonicheck
            PAYNET - Sofortüberweisung (PayNet)
            COD_CREDITWORTHINESS - Nachnahme/Bonicheck
            EBAY_EXPRESS - Ebay Express
            MONEYBOOKERS - Moneybookers
            CREDIT_CARD_MB - Kreditkarte Moneybookers
            DIRECT_DEBIT_MB - Lastschrift Moneybookers
            OTHERS - Sonstige*/

            //Shipping Costs
            //TODO: set shipping net
            $value->setShipping(Helper::convertDeString2Float($entity["ShippingInfo"]["ShippingCost"]));

            $netAmount += Helper::convertDeString2Float($entity["ShippingInfo"]["ShippingCost"]) / (1 + Helper::convertDeString2Float($entity["ShippingInfo"]["ShippingTaxRate"]) / 100);
            $value->setAmountNet($netAmount);

            //Addresses
            //TODO: add validation
            $billingAddress = new Address();

            $billingAddress->setFirstname($entity["BuyerInfo"]["BillingAddress"]["FirstName"]);
            $billingAddress->setLastname($entity["BuyerInfo"]["BillingAddress"]["LastName"]);

            if($entity["BuyerInfo"]["BillingAddress"]["Title"] == "Frau") {
                $billingAddress->setSalutation('mrs');
            } else {
                $billingAddress->setSalutation('mr');
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

            if(array_key_exists("ShippingAddress", $entity["BuyerInfo"])) {
                $shippingAddress = new Address();

                $shippingAddress->setFirstname($entity["BuyerInfo"]["ShippingAddress"]["FirstName"]);
                $shippingAddress->setLastname($entity["BuyerInfo"]["ShippingAddress"]["LastName"]);

                if(isset($entity["BuyerInfo"]["ShippingAddress"]["Title"]) && $entity["BuyerInfo"]["ShippingAddress"]["Title"] == "Frau") {
                    $shippingAddress->setSalutation('mrs');
                } else {
                    $shippingAddress->setSalutation('mr');
                }

                $shippingAddress->setCompany($entity["BuyerInfo"]["ShippingAddress"]["Company"]);
                $shippingAddress->setStreet($entity["BuyerInfo"]["ShippingAddress"]["Street"]);
                $shippingAddress->setAdditionalAddressLine1($entity["BuyerInfo"]["ShippingAddress"]["Street2"]);
                $shippingAddress->setZipcode($entity["BuyerInfo"]["ShippingAddress"]["PostalCode"]);
                $shippingAddress->setCity($entity["BuyerInfo"]["ShippingAddress"]["City"]);
                $shippingAddress->setCountry($entity["BuyerInfo"]["ShippingAddress"]["CountryISO"]);
                $shippingAddress->setPhone($entity["BuyerInfo"]["ShippingAddress"]["Phone"]);

                $value->setShippingAddress($shippingAddress);
            }
            else {
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
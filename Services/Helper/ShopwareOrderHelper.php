<?php

namespace FatchipAfterbuy\Services\Helper;

use FatchipAfterbuy\Components\Helper;
use FatchipAfterbuy\ValueObjects\Order;
use Shopware\Components\Model\ModelEntity;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Category\Category;
use Shopware\Models\Country\Country;
use Shopware\Models\Customer\Address;
use Shopware\Models\Customer\Customer;
use Shopware\Models\Customer\Group;
use Shopware\Models\Order\Detail;
use Shopware\Models\Shop\Shop;
use Shopware\Models\Tax\Tax;

class ShopwareOrderHelper extends AbstractHelper {

    protected $taxes;

    protected $paymentStates;

    protected $shippingStates;

    protected $paymentTypes;

    protected $countries;

    protected $detailStates;

    protected $targetGroup;

    protected $shippingType;

    public function preFetch() {
        $this->paymentStates = $this->getPaymentStates();
        $this->shippingStates = $this->getShippingStates();
        $this->paymentTypes = $this->getPaymentTypes();
        $this->countries = $this->getCountries();
        $this->detailStates = $this->getDetailStates();
        $this->targetGroup = $this->getDefaultGroup();
    }

    public function getUnexportedOrders() {
        $orders = $this->entityManager->createQueryBuilder()
            ->select(['orders'])
            ->from('\Shopware\Models\Order\Order', 'orders', 'orders.id')
            ->leftJoin('\Shopware\Models\Attribute\Order', 'attributes')
            ->where('orders.clearedDate > attributes.')
            ->getQuery()
            ->getResult();

        return $orders;

    }

    public function setShippingType(\Shopware\Models\Order\Order &$order, int $id) {
       $order->setDispatch($this->getShippingType($id));
    }

    public function getShippingType(int $id) {
        if($this->shippingType) {
            return $this->shippingType;
        }

        $this->shippingType = $this->entityManager->getRepository('\Shopware\Models\Dispatch\Dispatch')
            ->find($id);

        return $this->shippingType;
    }

    public function setPositions(Order $value, \Shopware\Models\Order\Order &$order) {
        $details = $order->getDetails();
        $details->clear();

        foreach($value->getPositions() as $position) {
            /**
             * @var OrderPosition $position
             */

            $detail = new Detail();
            $detail->setNumber($value->getExternalIdentifier());
            $detail->setTax($position->getTax());
            $detail->setQuantity($position->getQuantity());
            $detail->setPrice($position->getPrice());

            $tax = number_format($position->getTax(), 2);
            $detail->setTaxRate($tax);

            if($value->isShipped()) {
                $detail->setStatus($this->detailStates["3"]);
            } else {
                $detail->setStatus($this->detailStates["1"]);
            }

            $detail->setArticleNumber($position->getExternalIdentifier());
            $detail->setArticleName($position->getName());

            $tax = $this->getTax($position->getTax());

            $detail->setTaxRate($position->getTax());

            $detail->setTax($tax);
            $detail->setOrder($order);
            $detail->setArticleId(0);

            $details->add($detail);
        }
    }

    public function setAddress(Order $value, \Shopware\Models\Order\Order &$order, Customer $customer, $type = "billing") {
        if($type === "billing") {
            $entityClass = '\Shopware\Models\Order\Billing';
            $targetGetter = "getBilling";
            $sourceGetter = "getBillingAddress";
            $targetSetter = "setBilling";
        }
        else {
            $entityClass = '\Shopware\Models\Order\Shipping';
            $targetGetter = "getShipping";
            $targetSetter = "setShipping";

            if($value->getShippingAddress()) {
                $sourceGetter = "getShippingAddress";
            }
            else {
                $sourceGetter = "getBillingAddress";
            }
        }

        $address = $order->$targetGetter();

        if($address === null) {
            $address = new $entityClass();
        }

        if($type === "billing") {
            $address->setVatId($value->$sourceGetter()->getVatId());
        }


        $address->setSalutation($value->$sourceGetter()->getSalutation());
        $address->setFirstName($value->$sourceGetter()->getFirstname());
        $address->setLastName($value->$sourceGetter()->getLastname());
        $address->setStreet($value->$sourceGetter()->getStreet());
        $address->setAdditionalAddressLine1($value->$sourceGetter()->getAdditionalAddressLine1());
        $address->setAdditionalAddressLine2($value->$sourceGetter()->getAdditionalAddressLine2());
        $address->setZipcode($value->$sourceGetter()->getZipcode());
        $address->setCity($value->$sourceGetter()->getCity());
        $address->setCompany($value->$sourceGetter()->getCompany());
        $address->setDepartment($value->$sourceGetter()->getDepartment());
        $address->setCountry($this->countries[strtoupper($value->$sourceGetter()->getCountry())]);
        $address->setCustomer($customer);

        $order->$targetSetter($address);
    }

    public function setPaymentType(Order $value, \Shopware\Models\Order\Order &$order, array $config) {
        if($config["payment" . $value->getPaymentType()]) {
            $order->setPayment($this->paymentTypes[$config["payment" . $value->getPaymentType()]]);
        }
        else {
            //fallback: set first available payment type
            $order->setPayment(array_values($this->paymentTypes)[0]);
        }
    }

    public function setOrderTaxValues(Order $value, \Shopware\Models\Order\Order &$order) {
        if(!$value->getAmountNet()) {
            $order->setTaxFree(1);
            $order->setInvoiceAmountNet($value->getAmount());
            $order->setInvoiceShippingNet($value->getShipping());
        }
        else {
            $order->setTaxFree(0);
            $order->setInvoiceAmountNet($value->getAmountNet());
            $order->setInvoiceShippingNet($value->getShippingNet());
        }
    }

    public function setOrderMainValues(Order $value, \Shopware\Models\Order\Order &$order, Shop $shop) {
        /**
         * set main order values
         */
        $order->setInvoiceAmount($value->getAmount());
        $order->setInvoiceShipping($value->getShipping());
        $order->setInvoiceShippingTaxRate($value->getShippingTax());
        $order->setOrderTime($value->getCreateDate());
        $order->setTransactionId($value->getTransactionId());

        $order->setReferer("Afterbuy");
        $order->setTemporaryId($value->getExternalIdentifier());

        $order->setTransactionId($value->getTransactionId());
        $order->setCurrency($value->getCurrency());

        $order->setNet(0);

        $order->setShop($shop);
        $order->setLanguageSubShop($shop);

        //TODO: set correct values
        $order->setComment("");
        $order->setCustomerComment("");
        $order->setInternalComment("");
        $order->setTrackingCode("");
        $order->setCurrencyFactor(1);
    }

    public function setShippingStatus(Order $value, \Shopware\Models\Order\Order &$order) {
        if($value->isShipped()) {
            $order->setOrderStatus($this->shippingStates["completed"]);
        } else {
            $order->setOrderStatus($this->shippingStates["open"]);
        }
    }

    public function setPaymentStatus(Order $value, \Shopware\Models\Order\Order &$order) {
        if($value->getPaid() > 0) {
            $order->setPaymentStatus($this->paymentStates['partially_paid']);
        }
        if($value->getPaid() >= $value->getAmount()) {
            $order->setPaymentStatus($this->paymentStates["completely_paid"]);
        }
        if($value->getPaid() <= 0) {
            $order->setPaymentStatus($this->paymentStates["open"]);
        }
    }

    public function getShop(int $id) {
        return $this->entityManager->getRepository('\Shopware\Models\Shop\Shop')->find($id);
    }

    public function getCountries() {
        $countries = $this->entityManager->createQueryBuilder()
            ->select('countries')
            ->from('\Shopware\Models\Country\Country', 'countries', 'countries.iso')
            ->getQuery()
            ->getResult();

        return $countries;
    }

    public function getTax(float $rate) {

        $rate = number_format($rate, 2);

        if(!$this->taxes) {
            $this->getTaxes();
        }

        if(array_key_exists((string) $rate, $this->taxes)) {
            return $this->taxes[$rate];
        }

        $this->createTax($rate);
        $this->getTaxes();
    }

    public function getTaxes() {
        $taxes = $this->entityManager->createQueryBuilder()
            ->select('taxes')
            ->from('\Shopware\Models\Tax\Tax', 'taxes', 'taxes.tax')
            ->getQuery()
            ->getResult();

        $this->taxes = $taxes;
    }

    public function createTax(float $rate) {
        $tax = new Tax();
        $tax->setTax($rate);
        $tax->setName($rate);

        $this->entityManager->persist($tax);
        $this->entityManager->flush();
    }

    public function getPaymentStates() {
        $states = $this->entityManager->createQueryBuilder()
            ->select('states')
            ->from('\Shopware\Models\Order\Status', 'states', 'states.name')
            ->where('states.group = :group')
            ->setParameters(array('group' => 'payment'))
            ->getQuery()
            ->getResult();

        return $states;
    }

    public function getShippingStates() {
        $states = $this->entityManager->createQueryBuilder()
            ->select('states')
            ->from('\Shopware\Models\Order\Status', 'states', 'states.name')
            ->where('states.group = :group')
            ->setParameters(array('group' => 'state'))
            ->getQuery()
            ->getResult();

        return $states;
    }

    public function getDetailStates() {
        $states = $this->entityManager->createQueryBuilder()
            ->select('states')
            ->from('\Shopware\Models\Order\DetailStatus', 'states', 'states.id')
            ->getQuery()
            ->getResult();

        return $states;
    }

    public function getPaymentTypes() {
        $types = $this->entityManager->createQueryBuilder()
            ->select('types')
            ->from('\Shopware\Models\Payment\Payment', 'types', 'types.id')
            ->getQuery()
            ->getResult();

        return $types;
    }

    public function getCustomer(Order $order, \FatchipAfterbuy\ValueObjects\Address $billingAddress,
                                Shop $shop) {
        $customer = $this->entityManager->getRepository('\Shopware\Models\Customer\Customer')
            ->findOneBy(array('email' => $billingAddress->getEmail(), 'accountMode' => 1));

        if($customer) {
            return $customer;
        }

        return $this->createCustomer($order, $billingAddress, $shop);
    }

    public function createCustomer(Order $order, \FatchipAfterbuy\ValueObjects\Address $billingAddress,
                                   Shop $shop) {
        $customer = new Customer();

        $customer->setSalutation($billingAddress->getSalutation());
        $customer->setFirstname($billingAddress->getFirstname());
        $customer->setLastname($billingAddress->getLastname());
        $customer->setEmail($billingAddress->getEmail());
        $customer->setShop($shop);
        $customer->setAccountMode(1);
        $customer->setActive(true);
        $customer->setGroup($this->targetGroup);
        $customer->setNumber($order->getCustomerNumber());

        $address = new Address();

        $address->setFirstname($billingAddress->getFirstname());
        $address->setLastname($billingAddress->getLastname());
        $address->setSalutation($billingAddress->getSalutation());
        $address->setCountry($this->countries[strtoupper($billingAddress->getCountry())]);
        $address->setCompany($billingAddress->getCompany());
        $address->setDepartment($billingAddress->getDepartment());
        $address->setCity($billingAddress->getCity());
        $address->setZipcode($billingAddress->getZipcode());
        $address->setAdditionalAddressLine1($billingAddress->getAdditionalAddressLine1());
        $address->setCustomer($customer);

        $this->entityManager->persist($customer);
        $this->entityManager->persist($address);

        $customer->setDefaultBillingAddress($address);
        $customer->setDefaultShippingAddress($address);
        $this->entityManager->persist($customer);

        $this->entityManager->flush();

        return $customer;
    }

    public function getDefaultGroup() {
        $group = $this->entityManager->getRepository('\Shopware\Models\Customer\Group')->findOneBy(array());

        return $group;
    }



}
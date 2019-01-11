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
use Shopware\Models\Shop\Shop;
use Shopware\Models\Tax\Tax;

class ShopwareOrderHelper extends AbstractHelper {

    protected $taxes;

    protected $paymentStates;

    protected $shippingStates;

    public function preFetch() {
        $this->paymentStates = $this->getPaymentStates();
        $this->shippingStates = $this->getShippingStates();

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
                                Shop $shop, Group $group, Country $country) {
        $customer = $this->entityManager->getRepository('\Shopware\Models\Customer\Customer')
            ->findOneBy(array('email' => $billingAddress->getEmail(), 'accountMode' => 1));

        if($customer) {
            return $customer;
        }

        return $this->createCustomer($order, $billingAddress, $shop, $group, $country);
    }

    public function createCustomer(Order $order, \FatchipAfterbuy\ValueObjects\Address $billingAddress,
                                   Shop $shop, Group $group, Country $country) {
        $customer = new Customer();

        $customer->setSalutation($billingAddress->getSalutation());
        $customer->setFirstname($billingAddress->getFirstname());
        $customer->setLastname($billingAddress->getLastname());
        $customer->setEmail($billingAddress->getEmail());
        $customer->setShop($shop);
        $customer->setAccountMode(1);
        $customer->setActive(true);
        $customer->setGroup($group);
        $customer->setNumber($order->getCustomerNumber());

        $address = new Address();

        $address->setFirstname($billingAddress->getFirstname());
        $address->setLastname($billingAddress->getLastname());
        $address->setSalutation($billingAddress->getSalutation());
        $address->setCountry($country);
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
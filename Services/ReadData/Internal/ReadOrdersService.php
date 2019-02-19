<?php

namespace abaccAfterbuy\Services\ReadData\Internal;

use abaccAfterbuy\Services\Helper\ShopwareOrderHelper;
use Doctrine\Common\Collections\ArrayCollection;
use abaccAfterbuy\Services\ReadData\AbstractReadDataService;
use abaccAfterbuy\Services\ReadData\ReadDataInterface;
use abaccAfterbuy\ValueObjects\Address;
use abaccAfterbuy\ValueObjects\Order;
use abaccAfterbuy\ValueObjects\OrderPosition;
use Shopware\Models\Order\Detail;
use Shopware\Models\Order\Repository;

class ReadOrdersService extends AbstractReadDataService implements ReadDataInterface {
    /** @var ShopwareOrderHelper */
    public $helper;

    /**
     * @param array $filter
     * @return array|null
     */
    public function get(array $filter) :?array {
        $data = $this->read($filter);
        return $this->transform($data);
    }

    /**
     * transforms api input into valueObject (targetEntity)
     *
     * @param array $data
     * @return array|null
     */
    public function transform(array $data) :?array {
        $this->logger->debug('Receiving orders from shop', $data);

        if($this->targetEntity === null) {

            return null;
        }

        $targetData = array();

        foreach($data as $entity) {
            /**
             * @var Order $order
             */
            $order = new $this->targetEntity();

            /**
             * @var \Shopware\Models\Order\Order $entity
             */

            $positions = new ArrayCollection();

            foreach($entity->getDetails() as $position) {
                /**
                 * @var Detail $position
                 */
                $orderPosition = new OrderPosition();
                if($position->getEan()) {
                    $orderPosition->setExternalIdentifier($position->getEan());
                }
                $orderPosition->setInternalIdentifier($position->getNumber());
                $orderPosition->setName($position->getArticleName());
                $orderPosition->setPrice($position->getPrice());
                $orderPosition->setQuantity($position->getQuantity());
                $orderPosition->setTax($position->getTaxRate());

                $positions->add($orderPosition);
            }

            $order->setPositions($positions);


            $billingAddress = new Address();
            $billingAddress->setFirstname($entity->getBilling()->getFirstName());
            $billingAddress->setLastname($entity->getBilling()->getLastName());
            $billingAddress->setCompany($entity->getBilling()->getCompany());
            $billingAddress->setStreet($entity->getBilling()->getStreet());

            if($entity->getBilling()->getAdditionalAddressLine1()) {
                $billingAddress->setAdditionalAddressLine1($entity->getBilling()->getAdditionalAddressLine1());
            }
            $billingAddress->setZipcode($entity->getBilling()->getZipCode());
            $billingAddress->setCity($entity->getBilling()->getCity());
            $billingAddress->setCountry($entity->getBilling()->getCountry()->getIso());
            $billingAddress->setPhone($entity->getBilling()->getPhone());
            $billingAddress->setEmail($entity->getCustomer()->getEmail());

            if($entity->getCustomer()->getBirthday()) {
                $billingAddress->setBirthday($entity->getCustomer()->getBirthday());
            }

            $shippingAddress = new Address();
            $shippingAddress->setFirstname($entity->getShipping()->getFirstName());
            $shippingAddress->setLastname($entity->getShipping()->getLastName());
            $shippingAddress->setCompany($entity->getShipping()->getCompany());
            $shippingAddress->setStreet($entity->getShipping()->getStreet());
            if($entity->getShipping()->getAdditionalAddressLine1()) {
                $shippingAddress->setAdditionalAddressLine1($entity->getShipping()->getAdditionalAddressLine1());
            }
            $shippingAddress->setZipcode($entity->getShipping()->getZipCode());
            $shippingAddress->setCity($entity->getShipping()->getCity());
            $shippingAddress->setCountry($entity->getShipping()->getCountry()->getIso());

            $order->setBillingAddress($billingAddress);
            $order->setShippingAddress($shippingAddress);

            $order->setCreateDate($entity->getOrderTime());
            $order->setShipping($entity->getInvoiceShipping());

            try {
                $shippingType = $entity->getDispatch();
                $order->setShippingType($shippingType->getName());
            }
            catch(\Exception $e) {
                $order->setShippingType('Standard');
            }

            $order->setPaymentType($entity->getPayment()->getName());
            $order->setPaymentTypeId($entity->getPayment()->getId());

            if($entity->getTaxFree()) {
                $order->setTaxFree(true);
            }

            $order->setCustomerNumber($entity->getCustomer()->getNumber());
            $order->setInternalIdentifier($entity->getNumber());

            $order->setCurrency($entity->getCurrency());

            if($entity->getPaymentStatus()->getId() === 12) {
                $order->setPaid(true);
            }

            $order->setTransactionId($entity->getTransactionId());

            $targetData[] = $order;
        }

        return $targetData;
    }


    /**
     * provides api data. dummy data as used here can be used in tests
     *
     * @param array $filter
     * @return array
     */
    public function read(array $filter) :?array {

        /**
         * @var Repository $repo
         */
        $data = $this->helper->getUnexportedOrders();

        if(!$data) {
            $this->logger->error('No data received', array('Orders', 'Read', 'Internal'));
        }

        return $data;
    }
}
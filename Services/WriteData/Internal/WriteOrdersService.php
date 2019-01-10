<?php

namespace FatchipAfterbuy\Services\WriteData\Internal;

use FatchipAfterbuy\Services\Helper\AbstractHelper;
use FatchipAfterbuy\Services\Helper\ShopwareCategoryHelper;
use FatchipAfterbuy\Services\Helper\ShopwareOrderHelper;
use FatchipAfterbuy\Services\WriteData\AbstractWriteDataService;
use FatchipAfterbuy\Services\WriteData\WriteDataInterface;
use FatchipAfterbuy\ValueObjects\Order;
use FatchipAfterbuy\ValueObjects\OrderPosition;
use Shopware\Models\Order\Billing;
use Shopware\Models\Order\Detail;
use Shopware\Models\Order\Shipping;
use Shopware\Models\Shop\Shop;

class WriteOrdersService extends AbstractWriteDataService implements WriteDataInterface {

    /**
     * @var ShopwareOrderHelper $helper
     */

    /**
     * @var Shop
     */
    protected $targetShop;

    /**
     * @param array $data
     * @return mixed|void
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function put(array $data) {
        $data = $this->transform($data);
        return $this->send($data);
    }

    /**
     * transforms valueObject into final structure for storage
     * could may be moved into separate helper
     *
     * @param array $data
     * @return mixed|void
     */
    public function transform(array $data) {
        $this->targetShop = $this->helper->getShop($this->config['targetShop']);

        foreach($data as $value) {
            /**
             * @var Order $value
             */

            /**
             * @var \Shopware\Models\Order\Order $order
             */
            $order = $this->helper->getEntity($value->getExternalIdentifier(), 'number', false);

            /**
             * set main order values
             */
            $order->setInvoiceAmount($value->getAmount());
            $order->setInvoiceAmountNet($value->getAmountNet());
            $order->setInvoiceShipping($value->getShipping());
            $order->setShop($this->targetShop);
            $order->setLanguageSubShop($this->targetShop);
            $order->setReferer("Afterbuy");

            //TODO: set correct values
            $order->setInvoiceShippingNet($value->getShipping());
            $order->setTransactionId("");
            $order->setComment("");
            $order->setCustomerComment("");
            $order->setInternalComment("");
            $order->setNet(0);
            $order->setTaxFree(0);
            $order->setTemporaryId($value->getExternalIdentifier());

            $order->setTrackingCode("");

            $order->setCurrency('EUR');
            $order->setCurrencyFactor(1);

            /**
             * set billing address
             */

            $billingAddress = $order->getBilling();

            if($billingAddress === null) {
                $billingAddress = new Billing();
            }

            $billingAddress->setVatId($value->getBillingAddress()->getVatId());
            $billingAddress->setSalutation($value->getBillingAddress()->getSalutation());
            $billingAddress->setFirstName($value->getBillingAddress()->getFirstname());
            $billingAddress->setLastName($value->getBillingAddress()->getLastname());
            $billingAddress->setStreet($value->getBillingAddress()->getStreet());
            $billingAddress->setAdditionalAddressLine1($value->getBillingAddress()->getAdditionalAddressLine1());
            $billingAddress->setAdditionalAddressLine2($value->getBillingAddress()->getAdditionalAddressLine2());
            $billingAddress->setZipcode($value->getBillingAddress()->getZipcode());
            $billingAddress->setCity($value->getBillingAddress()->getCity());
            $billingAddress->setCompany($value->getBillingAddress()->getCompany());
            $billingAddress->setDepartment($value->getBillingAddress()->getDepartment());


            $billingAddress->setCountry($this->entityManager->getRepository('\Shopware\Models\Country\Country')->find(2));

            $order->setBilling($billingAddress);

            //TODO: what if we got no customer?
            $billingAddress->setCustomer($this->entityManager->getRepository('\Shopware\Models\Customer\Customer')->find(1));
            //TODO: phone, vatID, country, mail

            /**
             * set shipping address
             */

            $shippingAddress = $order->getShipping();

            if($shippingAddress === null) {
                $shippingAddress = new Shipping();
            }

            if($value->getShippingAddress()) {
                $getter = "getShippingAddress";
            }
            else {
                $getter = "getBillingAddress";
            }

            $shippingAddress->setSalutation($value->$getter()->getSalutation());
            $shippingAddress->setFirstName($value->$getter()->getFirstname());
            $shippingAddress->setLastName($value->$getter()->getLastname());
            $shippingAddress->setStreet($value->$getter()->getStreet());
            $shippingAddress->setAdditionalAddressLine1($value->$getter()->getAdditionalAddressLine1());
            $shippingAddress->setAdditionalAddressLine2($value->$getter()->getAdditionalAddressLine2());
            $shippingAddress->setZipcode($value->$getter()->getZipcode());
            $shippingAddress->setCity($value->$getter()->getCity());
            $shippingAddress->setCompany($value->$getter()->getCompany());
            $shippingAddress->setDepartment($value->$getter()->getDepartment());
            //TODO: phone, country, mail

            $order->setShipping($shippingAddress);

            /**
             * set and update positions
             */

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
                $detail->setStatus($this->entityManager->getRepository('\Shopware\Models\Order\DetailStatus')->find(1));
                $detail->setArticleNumber($position->getExternalIdentifier());
                $detail->setArticleName($position->getName());

                //TODO: cache and helper / create new if needed
                $tax = $this->entityManager->getRepository('\Shopware\Models\Tax\Tax')->findOneBy(array('tax' => $tax));

                $detail->setTaxRate($position->getTax());

                $detail->setTax($tax);
                $detail->setOrder($order);
                $detail->setArticleId(0);

                $details->add($detail);
            }

            //TODO: taxfree
            //TODO: set shipping
            $order->setDispatch($this->entityManager->getRepository('\Shopware\Models\Dispatch\Dispatch')->find(9));

            //TODO: set payment
            $order->setPayment($this->entityManager->getRepository('\Shopware\Models\Payment\Payment')->find(5));



            //TODO: set status
            //TODO: set payment status / only import paid
            $order->setOrderStatus($this->entityManager->getRepository('\Shopware\Models\Order\Status')->find(1));
            $order->setPaymentStatus($this->entityManager->getRepository('\Shopware\Models\Order\Status')->find(1));

            $this->entityManager->persist($order);
        }
    }


    /**
     * @param $targetData
     * @return mixed|void
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function send($targetData) {
        $this->entityManager->flush();
    }
}
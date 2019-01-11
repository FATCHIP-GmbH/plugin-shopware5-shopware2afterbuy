<?php

namespace FatchipAfterbuy\Services\WriteData\Internal;

use FatchipAfterbuy\Services\Helper\AbstractHelper;
use FatchipAfterbuy\Services\Helper\ShopwareCategoryHelper;
use FatchipAfterbuy\Services\Helper\ShopwareOrderHelper;
use FatchipAfterbuy\Services\WriteData\AbstractWriteDataService;
use FatchipAfterbuy\Services\WriteData\WriteDataInterface;
use FatchipAfterbuy\ValueObjects\Order;
use FatchipAfterbuy\ValueObjects\OrderPosition;
use Shopware\Models\Customer\Group;
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
     * @var array
     */
    protected $countries;

    /**
     * @var array
     */
    protected $paymentStates;

    /**
     * @var array
     */
    protected $shippingStates;

    /**
     * @var array
     */
    protected $detailStates;

    /**
     * @var array
     */
    protected $paymentTypes;

    /**
     * @var Group
     */
    protected $targetGroup;

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
        $this->countries = $this->helper->getCountries();
        $this->paymentStates = $this->helper->getPaymentStates();
        $this->shippingStates = $this->helper->getShippingStates();
        $this->detailStates = $this->helper->getDetailStates();
        $this->paymentTypes = $this->helper->getPaymentTypes();
        $this->targetGroup = $this->helper->getDefaultGroup();

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
            $order->setInvoiceShipping($value->getShipping());
            $order->setInvoiceShippingTaxRate($value->getShippingTax());
            $order->setOrderTime($value->getCreateDate());
            $order->setTransactionId($value->getTransactionId());

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

            /**
             * set payment status
             */
            if($value->getPaid() > 0) {
                $order->setPaymentStatus($this->paymentStates['partially_paid']);
            }
            if($value->getPaid() >= $value->getAmount()) {
                $order->setPaymentStatus($this->paymentStates["completely_paid"]);
            }
            if($value->getPaid() <= 0) {
                $order->setPaymentStatus($this->paymentStates["open"]);
            }

            /**
             * set shipping status
             */
            if($value->isShipped()) {
                $order->setOrderStatus($this->shippingStates["completed"]);
            } else {
                $order->setOrderStatus($this->shippingStates["open"]);
            }

            /**
             * set payment type
             */
            if($this->config["payment" . $value->getPaymentType()]) {
                $order->setPayment($this->paymentTypes[$this->config["payment" . $value->getPaymentType()]]);
            }
            else {
                //fallback: set first available payment type
                $order->setPayment(array_values($this->paymentTypes)[0]);
            }

            $order->setShop($this->targetShop);
            $order->setLanguageSubShop($this->targetShop);
            $order->setReferer("Afterbuy");
            $order->setTemporaryId($value->getExternalIdentifier());


            $order->setTransactionId($value->getTransactionId());
            $order->setCurrency($value->getCurrency());
            $order->setNet(0);

            //TODO: set correct values
            $order->setComment("");
            $order->setCustomerComment("");
            $order->setInternalComment("");
            $order->setTrackingCode("");
            $order->setCurrencyFactor(1);

            $customer = $this->helper->getCustomer($value, $value->getBillingAddress(), $this->targetShop, $this->targetGroup, $this->countries[strtoupper($value->getBillingAddress()->getCountry())]);
            $order->setCustomer($customer);

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
            $billingAddress->setCountry($this->countries[strtoupper($value->getBillingAddress()->getCountry())]);
            $billingAddress->setCustomer($customer);
            //TODO: phone,

            $order->setBilling($billingAddress);

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
            $shippingAddress->setCustomer($customer);
            //TODO: phone, mail

            //log and ignore order if country is not setup in shop
            if(!$this->countries[strtoupper($value->getBillingAddress()->getCountry())] || !$this->countries[strtoupper($value->$getter()->getCountry())]) {
                $this->logger->error('Country is not available in Shop config.', array($value->getBillingAddress()->getCountry(), $value->$getter()->getCountry()));
                continue;
            }

            $shippingAddress->setCountry($this->countries[strtoupper($value->$getter()->getCountry())]);

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

                if($value->isShipped()) {
                    $detail->setStatus($this->detailStates["3"]);
                } else {
                    $detail->setStatus($this->detailStates["1"]);
                }

                $detail->setArticleNumber($position->getExternalIdentifier());
                $detail->setArticleName($position->getName());

                //TODO: cache and helper / create new if needed
                $tax = $this->helper->getTax($position->getTax());

                $detail->setTaxRate($position->getTax());

                $detail->setTax($tax);
                $detail->setOrder($order);
                $detail->setArticleId(0);

                $details->add($detail);
            }

            //TODO: set shipping
            $order->setDispatch($this->entityManager->getRepository('\Shopware\Models\Dispatch\Dispatch')->find(9));

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
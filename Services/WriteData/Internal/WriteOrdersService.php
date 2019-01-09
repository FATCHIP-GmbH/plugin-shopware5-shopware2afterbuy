<?php

namespace FatchipAfterbuy\Services\WriteData\Internal;

use FatchipAfterbuy\Services\Helper\AbstractHelper;
use FatchipAfterbuy\Services\Helper\ShopwareCategoryHelper;
use FatchipAfterbuy\Services\WriteData\AbstractWriteDataService;
use FatchipAfterbuy\Services\WriteData\WriteDataInterface;
use FatchipAfterbuy\ValueObjects\Category;
use FatchipAfterbuy\ValueObjects\Order;
use FatchipAfterbuy\ValueObjects\OrderPosition;
use Shopware\Models\Customer\Address;
use Shopware\Models\Order\Billing;
use Shopware\Models\Order\Detail;
use Shopware\Models\Order\Shipping;

class WriteOrdersService extends AbstractWriteDataService implements WriteDataInterface {

    /**
     * @var string $identifier
     */
    protected $identifier;

    /**
     * @var bool $isAttribute
     */
    protected $isAttribute;

    /**
     * @param AbstractHelper $helper
     * @param string $identifier
     * @param bool $isAttribute
     */
    public function initHelper(string $identifier, bool $isAttribute) {
        $this->identifier = $identifier;
        $this->isAttribute = $isAttribute;
    }

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

        foreach($data as $value) {
            /**
             * @var Order $value
             */

            /**
             * @var \Shopware\Models\Order\Order $order
             */
            //TODO: move to helper
            $order = $this->entityManager->getRepository($this->targetRepository)->findOneBy(array($this->identifier => $value->getExternalIdentifier()));

            if(!$order) {
                $order = new $this->targetRepository();

                //TODO: set identifier
            }

            //TODO: set date

            $order->setNumber($value->getExternalIdentifier());
            $order->setInvoiceAmount($value->getAmount());
            $order->setInvoiceAmountNet($value->getAmountNet());

            $order->setInvoiceShipping($value->getShipping());

            //TODO: set correct values
            $order->setInvoiceShippingNet($value->getShipping());
            $order->setTransactionId("");
            $order->setComment("");
            $order->setCustomerComment("");
            $order->setInternalComment("");
            $order->setNet(0);
            $order->setTaxFree(1);
            $order->setTemporaryId($value->getExternalIdentifier());
            $order->setReferer("Afterbuy");
            $order->setTrackingCode("");
            $order->setLanguageIso('DE');
            $order->setCurrency('EUR');
            $order->setCurrencyFactor(1);

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
            $billingAddress->setCustomer($this->entityManager->getRepository('\Shopware\Models\Customer\Customer')->find(1));
            //TODO: phone, vatID, country, mail



            if($value->getBillingAddress() === $value->getShippingAddress()) {
                $order->setShipping($order->getBilling());
            }
            else {
                $shippingAddress = $order->getShipping();

                if($shippingAddress === null) {
                    $shippingAddress = new Shipping();
                }

                //$shippingAddress->setVatId($value->getShippingAddress()->getVatId());
                $shippingAddress->setSalutation($value->getShippingAddress()->getSalutation());
                $shippingAddress->setFirstName($value->getShippingAddress()->getFirstname());
                $shippingAddress->setLastName($value->getShippingAddress()->getLastname());
                $shippingAddress->setStreet($value->getShippingAddress()->getStreet());
                $shippingAddress->setAdditionalAddressLine1($value->getShippingAddress()->getAdditionalAddressLine1());
                $shippingAddress->setAdditionalAddressLine2($value->getShippingAddress()->getAdditionalAddressLine2());
                $shippingAddress->setZipcode($value->getShippingAddress()->getZipcode());
                $shippingAddress->setCity($value->getShippingAddress()->getCity());
                $shippingAddress->setCompany($value->getShippingAddress()->getCompany());
                $shippingAddress->setDepartment($value->getShippingAddress()->getDepartment());
                //TODO: phone, vatID, country, mail
            }

            //TODO: set order details
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

                $detail->setTax($tax);
                $detail->setOrder($order);
                $detail->setArticleId(0);


                $details->add($detail);
            }

            //TODO: set amount
            //TODO: set positions
            //TODO: set shipping
            $order->setDispatch($this->entityManager->getRepository('\Shopware\Models\Dispatch\Dispatch')->find(9));

            //TODO: set payment
            $order->setPayment($this->entityManager->getRepository('\Shopware\Models\Payment\Payment')->find(5));

            //TODO: config target subshop
            $order->setShop($this->entityManager->getRepository('\Shopware\Models\Shop\Shop')->find(1));

            //TODO: set status
            //TODO: set payment status / only import paid
            $order->setOrderStatus($this->entityManager->getRepository('\Shopware\Models\Order\Status')->find(1));
            $order->setPaymentStatus($this->entityManager->getRepository('\Shopware\Models\Order\Status')->find(1));

            $this->entityManager->persist($billingAddress);
            $order->setBilling($billingAddress);
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
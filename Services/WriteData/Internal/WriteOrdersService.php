<?php

namespace FatchipAfterbuy\Services\WriteData\Internal;

use FatchipAfterbuy\Services\Helper\AbstractHelper;
use FatchipAfterbuy\Services\Helper\ShopwareCategoryHelper;
use FatchipAfterbuy\Services\WriteData\AbstractWriteDataService;
use FatchipAfterbuy\Services\WriteData\WriteDataInterface;
use FatchipAfterbuy\ValueObjects\Category;
use FatchipAfterbuy\ValueObjects\Order;
use Shopware\Models\Customer\Address;

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

            $order = $this->entityManager->getRepository($this->targetRepository)->findOneBy(array($this->identifier => $value->getExternalIdentifier()));

            if(!$order) {
                $order = new $this->targetRepository();

                //TODO: set identifier
            }

            $order->setNumber($value->getExternalIdentifier());
            $order->setInvoiceAmount($value->getAmount());

            if(!$order->getBilling()) {
                $order->setBilling(new Address());
            }

            $order->getBilling()->setVatId($value->getBillingAddress()->getVatId());
            $order->getBilling()->setSalutation($value->getBillingAddress()->getSalutation());
            $order->getBilling()->setStreet($value->getBillingAddress()->getStreet());


            $order->getBilling()->setStreet($value->getBillingAddress()->getStreet());
            $order->getBilling()->setStreet($value->getBillingAddress()->getStreet());
            $order->getBilling()->setStreet($value->getBillingAddress()->getStreet());
            $order->getBilling()->setStreet($value->getBillingAddress()->getStreet());
            $order->getBilling()->setStreet($value->getBillingAddress()->getStreet());
            $order->getBilling()->setStreet($value->getBillingAddress()->getStreet());
            $order->getBilling()->setStreet($value->getBillingAddress()->getStreet());
            $order->getBilling()->setStreet($value->getBillingAddress()->getStreet());
            $order->getBilling()->setStreet($value->getBillingAddress()->getStreet());
            $order->getBilling()->setStreet($value->getBillingAddress()->getStreet());
            $order->getBilling()->setStreet($value->getBillingAddress()->getStreet());
            $order->getBilling()->setStreet($value->getBillingAddress()->getStreet());
            $order->getBilling()->setStreet($value->getBillingAddress()->getStreet());
            $order->getBilling()->setStreet($value->getBillingAddress()->getStreet());


            //TODO: set amount
            //TODO: set billing
            //TODO: set shipping
            //TODO: set positions

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
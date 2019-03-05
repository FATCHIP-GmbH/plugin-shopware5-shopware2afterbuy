<?php

namespace viaebShopwareAfterbuy\Services\Helper;

use Doctrine\ORM\OptimisticLockException;
use viaebShopwareAfterbuy\ValueObjects\Address as ValueAddress;
use viaebShopwareAfterbuy\ValueObjects\Order as ValueOrder;
use viaebShopwareAfterbuy\ValueObjects\OrderPosition;
use Shopware\Models\Customer\Address;
use Shopware\Models\Customer\Customer;
use Shopware\Models\Order\Billing;
use Shopware\Models\Order\Detail;
use Shopware\Models\Order\Order as ShopwareOrder;
use Shopware\Models\Order\Status as OrderStatus;
use Shopware\Models\Shop\Shop;
use Shopware\Models\Customer\Group;
use viaebShopwareAfterbuy\Models\Status;
use Shopware\Models\Dispatch\Dispatch;
use Shopware\Models\Order\Shipping;
use Shopware\Models\Country\Country;
use Shopware\Models\Order\DetailStatus;
use Shopware\Models\Payment\Payment;

class ShopwareOrderHelper extends AbstractHelper
{

    protected $paymentStates;

    protected $shippingStates;

    /** @var Payment[] */
    protected $paymentTypes;

    protected $countries;

    protected $detailStates;

    protected $targetGroup;

    protected $shippingType;

    public function preFetch()
    {
        $this->paymentStates = $this->getPaymentStates();
        $this->shippingStates = $this->getShippingStates();
        $this->paymentTypes = $this->getPaymentTypes();
        $this->countries = $this->getCountries();
        $this->detailStates = $this->getDetailStates();
        $this->targetGroup = $this->getDefaultGroup();
    }

    public function getABCountryCodes(): array
    {
        return array(
            'AF' => 'AFG',
            'EG' => 'ET',
            'AX' => 'AX',
            'AL' => 'AL',
            'DZ' => 'DZ',
            'AS' => 'USA',
            'AD' => 'AND',
            'AO' => 'ANG',
            'AI' => 'AXA',
            'AQ' => 'AY',
            'AG' => 'AG',
            'GQ' => 'GQ',
            'AR' => 'RA',
            'AM' => 'AM',
            'AW' => 'ARU',
            'AZ' => 'AZ',
            'ET' => 'ETH',
            'AU' => 'AUS',
            'BS' => 'BS',
            'BH' => 'BRN',
            'BD' => 'BD',
            'BB' => 'BDS',
            'BY' => 'BY',
            'BE' => 'B',
            'BZ' => 'BZ',
            'BJ' => 'BJ',
            'BM' => 'BD',
            'BT' => 'BHT',
            'BO' => 'BOL',
            'BQ' => 'NL',
            'BA' => 'BIH',
            'BW' => 'RB',
            'BV' => 'BV',
            'BR' => 'BR',
            'IO' => 'IO',
            'BN' => 'BRU',
            'BG' => 'BG',
            'BF' => 'BF',
            'BI' => 'RU',
            'CL' => 'RCH',
            'CN' => 'CHN',
            'CK' => 'CW',
            'CR' => 'CR',
            'CW' => 'UC',
            'DK' => 'DK',
            'CD' => 'CGO',
            'DE' => 'D',
            'DM' => 'WD',
            'DO' => 'DOM',
            'DJ' => 'DJI',
            'EC' => 'EC',
            'SV' => 'ES',
            'CI' => 'CI',
            'ER' => 'ER',
            'EE' => 'EST',
            'FK' => 'FK',
            'FO' => 'FO',
            'FJ' => 'FJI',
            'FI' => 'FIN',
            'FR' => 'F',
            'GF' => 'FG',
            'PF' => 'FP',
            'TF' => 'FS',
            'GA' => 'G',
            'GM' => 'WAG',
            'GE' => 'GE',
            'GH' => 'GH',
            'GI' => 'GBZ',
            'GD' => 'WG',
            'GR' => 'GR',
            'GL' => 'KN',
            'GP' => 'GP',
            'GU' => 'GQ',
            'GT' => 'GCA',
            'GG' => 'GBG',
            'GN' => 'RG',
            'GW' => 'GUB',
            'GY' => 'GUY',
            'HT' => 'RH',
            'HM' => 'HM',
            'HN' => 'HN',
            'HK' => 'HK',
            'IN' => 'IND',
            'ID' => 'RI',
            'IQ' => 'IRQ',
            'IR' => 'IR',
            'IE' => 'IRL',
            'IS' => 'IS',
            'IL' => 'IL',
            'IT' => 'I',
            'JM' => 'JA',
            'JP' => 'J',
            'YE' => 'YEM',
            'JE' => 'GBJ',
            'JO' => 'JOR',
            'VG' => 'VG',
            'VI' => 'VQ',
            'KY' => 'CJ',
            'KH' => 'K',
            'CM' => 'CAM',
            'CA' => 'CDN',
            'CV' => 'CV',
            'KZ' => 'KZ',
            'QA' => 'Q',
            'KE' => 'EAK',
            'KG' => 'KS',
            'KI' => 'KIR',
            'CC' => 'CK',
            'CO' => 'CO',
            'KM' => 'COM',
            'CG' => 'RCB',
            'XK' => 'RKS',
            'HR' => 'HR',
            'CU' => 'C',
            'KW' => 'KWT',
            'LA' => 'LAO',
            'LS' => 'LS',
            'LV' => 'LV',
            'LB' => 'RL',
            'LR' => 'LB',
            'LY' => 'LAR',
            'LI' => 'FL',
            'LT' => 'LT',
            'LU' => 'L',
            'MO' => 'MC',
            'MG' => 'RM',
            'MW' => 'MW',
            'MY' => 'MAL',
            'MV' => 'MV',
            'ML' => 'RMM',
            'MT' => 'M',
            'MA' => 'MA',
            'MH' => 'MH',
            'MQ' => 'MB',
            'MR' => 'RIM',
            'MU' => 'MS',
            'YT' => 'MF',
            'MK' => 'MK',
            'MX' => 'MEX',
            'FM' => 'FSM',
            'MD' => 'MD',
            'MC' => 'MC',
            'MN' => 'MGL',
            'ME' => 'MNE',
            'MS' => 'MH',
            'MZ' => 'MOC',
            'MM' => 'MYA',
            'NA' => 'NAM',
            'NR' => 'NAU',
            'NP' => 'NEP',
            'NC' => 'NCL',
            'NZ' => 'NZ',
            'NI' => 'NIC',
            'NL' => 'NL',
            'NE' => 'RN',
            'NG' => 'NGR',
            'NU' => 'NE',
            'KP' => 'KP',
            'MP' => 'CQ',
            'NF' => 'NF',
            'NO' => 'N',
            'OM' => 'OM',
            'AT' => 'A',
            'TL' => 'TL',
            'PK' => 'PK',
            'PS' => 'WB',
            'PW' => 'PAL',
            'PA' => 'PA',
            'PG' => 'PNG',
            'PY' => 'PY',
            'PE' => 'PE',
            'PH' => 'RP',
            'PN' => 'PC',
            'PL' => 'PL',
            'PT' => 'P',
            'PR' => 'PRI',
            'RE' => 'RE',
            'RW' => 'RWA',
            'RO' => 'RUM',
            'RU' => 'RUS',
            'MF' => 'F',
            'SB' => 'SOL',
            'ZM' => 'Z',
            'WS' => 'WS',
            'SM' => 'RSM',
            'BL' => 'TB',
            'ST' => 'STP',
            'SA' => 'KSA',
            'SE' => 'S',
            'CH' => 'CH',
            'SN' => 'SN',
            'RS' => 'SRB',
            'SC' => 'SY',
            'SL' => 'WAL',
            'ZW' => 'ZW',
            'SG' => 'SGP',
            'SX' => 'NN',
            'SK' => 'SK',
            'SI' => 'SLO',
            'SO' => 'SO',
            'ES' => 'E',
            'LK' => 'CL',
            'SH' => 'SH',
            'KN' => 'KAN',
            'LC' => 'WL',
            'PM' => 'SB',
            'VC' => 'WV',
            'ZA' => 'ZA',
            'SD' => 'SUD',
            'GS' => 'SX',
            'KR' => 'ROK',
            'SS' => 'SSD',
            'SR' => 'SME',
            'SJ' => 'SV',
            'SZ' => 'SD',
            'SY' => 'SYR',
            'TJ' => 'TJ',
            'TW' => 'RC',
            'TZ' => 'EAT',
            'TH' => 'T',
            'TG' => 'TG',
            'TK' => 'TL',
            'TO' => 'TON',
            'TT' => 'TT',
            'TD' => 'TD',
            'CZ' => 'CZ',
            'TN' => 'TN',
            'TR' => 'TR',
            'TM' => 'TM',
            'TC' => 'TK',
            'TV' => 'TUV',
            'UG' => 'EAU',
            'UA' => 'UA',
            'HU' => 'H',
            'UY' => 'ROU',
            'UZ' => 'UZ',
            'VU' => 'VAN',
            'VA' => 'V',
            'VE' => 'YV',
            'AE' => 'UAE',
            'US' => 'USA',
            'GB' => 'GBM',
            'VN' => 'VN',
            'WF' => 'WF',
            'CX' => 'KT',
            'EH' => 'WSA',
            'CF' => 'RCA',
            'CY' => 'CY',
        );
    }


    /**
     * @param array $values
     */
    public function setAfterBuyIds(array $values)
    {
        $orders = $this->entityManager->createQueryBuilder()
            ->select(['orders'])
            ->from(ShopwareOrder::class, 'orders', 'orders.id')
            ->where('orders.number IN(:numbers)')
            ->setParameter('numbers', array_keys($values))
            ->getQuery()
            ->getResult();

        foreach ($orders as $order) {
            /**
             * @var ShopwareOrder $order
             */
            $order->getAttribute()->setAfterbuyOrderId($values[$order->getNumber()]);

            $this->entityManager->persist($order);
        }

        try {
            $this->entityManager->flush();
        } catch (OptimisticLockException $e) {
            $this->logger->error('Error storing afterbuy ids');
        }
    }

    /**
     * @return array
     */
    public function getUnexportedOrders()
    {
        $orders = $this->entityManager->createQueryBuilder()
            ->select(['orders'])
            ->from(ShopwareOrder::class, 'orders', 'orders.id')
            ->leftJoin('orders.attribute', 'attributes')
            ->where('attributes.afterbuyOrderId IS NULL')
            ->orWhere("attributes.afterbuyOrderId = ''")
            ->getQuery()
            ->setMaxResults(200)
            ->getResult();

        return $orders;

    }

    public function isFullfilled(ShopwareOrder $order)
    {
        $completelyPaid = $order->getPaymentStatus()->getId() === 12;
        $completelyDelivered = $order->getOrderStatus()->getId() === 7;
        $completed = $order->getOrderStatus()->getId() === 2;

        return $completelyPaid && ($completelyDelivered || $completed);
    }

    public function getNewFullfilledOrders()
    {
        $lastExport = $this->entityManager->getRepository(Status::class)->find(1);

        if ($lastExport) {
            $lastExport = $lastExport->getLastStatusExport();
        }

        $orders = $this->entityManager->createQueryBuilder()
            ->select(['orders', 'history'])
            ->from(ShopwareOrder::class, 'orders', 'orders.id')
            ->leftJoin('orders.attribute', 'attributes')
            ->leftJoin('orders.history', 'history')
            ->where('attributes.afterbuyOrderId IS NOT NULL')
            ->andWhere("attributes.afterbuyOrderId != ''")
            ->andWhere('orders.cleared = 12')
            ->andWhere('orders.status = 7')
            ->orWhere('orders.status = 2')
            ->having('history.changeDate  >= :lastExport')
            ->andHaving('history.paymentStatusId = 12')
            ->andHaving('history.orderStatusId = 2 OR history.orderStatusId = 7')
            ->setParameters(array('lastExport' => $lastExport))
            ->getQuery()
            ->setMaxResults(145)
            ->getResult();

        return $orders;
    }

    public function getUnfullfilledOrders() {

        $orders = $this->entityManager->createQueryBuilder()
            ->select(['attributes.afterbuyOrderId'])
            ->from(\Shopware\Models\Attribute\Order::class, 'attributes')
            ->leftJoin('attributes.order', 'orders')
            ->where('attributes.afterbuyOrderId IS NOT NULL')
            ->andWhere("attributes.afterbuyOrderId != ''")
            ->andWhere('orders.status = 0')
            ->getQuery()
            ->setMaxResults(250)
            ->getScalarResult();

        return $orders;
    }

    /**
     * @param ShopwareOrder $order
     * @param int           $id
     */
    public function setShippingType(ShopwareOrder &$order, int $id): void
    {
        $order->setDispatch($this->getShippingType($id));
    }

    /**
     * @param int $id
     *
     * @return object|\Shopware\Models\Dispatch\Dispatch|null
     */
    public function getShippingType(int $id)
    {
        if ($this->shippingType) {
            return $this->shippingType;
        }

        $this->shippingType = $this->entityManager->getRepository(Dispatch::class)
            ->find($id);

        return $this->shippingType;
    }

    public function setPositions(ValueOrder $value, ShopwareOrder &$order): void
    {
        $details = $order->getDetails();
        $details->clear();

        foreach ($value->getPositions() as $position) {
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

            if ($value->isShipped()) {
                $detail->setStatus($this->detailStates['3']);
            } else {
                $detail->setStatus($this->detailStates['1']);
            }

            if ($position->getInternalIdentifier() !== null) {
                $detail->setArticleNumber($position->getInternalIdentifier());
            } else {
                $detail->setArticleNumber($position->getExternalIdentifier());
            }


            $detail->setArticleName($position->getName());

            $tax = $this->getTax($position->getTax());

            $detail->setTaxRate($position->getTax());

            $detail->setTax($tax);
            $detail->setOrder($order);
            $detail->setArticleId(0);

            $details->add($detail);
        }
    }

    public function setAddress(ValueOrder $value, ShopwareOrder &$order, Customer $customer, $type = 'billing')
    {
        if ($type === 'billing') {
            $entityClass = Billing::class;
            $targetGetter = 'getBilling';
            $sourceGetter = 'getBillingAddress';
            $targetSetter = 'setBilling';
        } else {
            $entityClass = Shipping::class;
            $targetGetter = 'getShipping';
            $targetSetter = 'setShipping';

            if ($value->getShippingAddress()) {
                $sourceGetter = 'getShippingAddress';
            } else {
                $sourceGetter = 'getBillingAddress';
            }
        }

        /** @var Billing $address */
        $address = $order->$targetGetter();

        if ($address === null) {
            $address = new $entityClass();
        }

        if ($type === 'billing') {
            $address->setVatId($value->$sourceGetter()->getVatId());
        }


        $address->setSalutation($value->$sourceGetter()->getSalutation());
        $address->setFirstName($value->$sourceGetter()->getFirstname());
        $address->setLastName($value->$sourceGetter()->getLastname());
        $address->setStreet($value->$sourceGetter()->getStreet());
        $address->setAdditionalAddressLine1($value->$sourceGetter()->getAdditionalAddressLine1());
        $address->setAdditionalAddressLine2($value->$sourceGetter()->getAdditionalAddressLine2());
        $address->setZipCode($value->$sourceGetter()->getZipcode());
        $address->setCity($value->$sourceGetter()->getCity());
        $address->setCompany($value->$sourceGetter()->getCompany());
        $address->setDepartment($value->$sourceGetter()->getDepartment());
        $address->setCountry($this->countries[strtoupper($value->$sourceGetter()->getCountry())]);
        $address->setCustomer($customer);

        $order->$targetSetter($address);
    }

    /**
     * @param ValueOrder $value
     * @param ShopwareOrder $order
     * @param array $config
     */
    public function setPaymentType(ValueOrder $value, ShopwareOrder &$order, array $config)
    {
        if ($config['payment' . $value->getPaymentType()]) {
            $order->setPayment($this->paymentTypes[$config['payment' . $value->getPaymentType()]]);
        } else {
            $order->setPayment(array_values($this->paymentTypes)[0]);
        }
    }

    public function setOrderTaxValues(ValueOrder $value, ShopwareOrder &$order)
    {
        if ( ! $value->getAmountNet()) {
            $order->setTaxFree(1);
            $order->setInvoiceAmountNet($value->getAmount());
            $order->setInvoiceShippingNet($value->getShipping());
        } else {
            $order->setTaxFree(0);
            $order->setInvoiceAmountNet($value->getAmountNet());
            $order->setInvoiceShippingNet($value->getShippingNet());
        }
    }

    public function setOrderMainValues(ValueOrder $value, ShopwareOrder &$order, Shop $shop)
    {
        /**
         * set main order values
         */
        $order->setInvoiceAmount($value->getAmount());
        $order->setInvoiceShipping($value->getShipping());
        $order->setInvoiceShippingTaxRate($value->getShippingTax());
        $order->setOrderTime($value->getCreateDate());
        $order->setTransactionId($value->getTransactionId());

        $order->setReferer('Afterbuy');
        $order->setTemporaryId($value->getExternalIdentifier());

        $order->setTransactionId($value->getTransactionId());
        $order->setCurrency($value->getCurrency());

        $order->setNet(0);

        $order->setShop($shop);
        $order->setLanguageSubShop($shop);

        $order->getAttribute()->setAfterbuyOrderId($value->getExternalIdentifier());

        $order->setComment('');
        $order->setCustomerComment('');
        $order->setInternalComment('');
        $order->setTrackingCode('');
        $order->setCurrencyFactor(1);
    }

    /**
     * @param ValueOrder    $value
     * @param ShopwareOrder $order
     */
    public function setShippingStatus(ValueOrder $value, ShopwareOrder &$order)
    {
        if ($value->isShipped()) {
            $order->setOrderStatus($this->shippingStates['completed']);
        } else {
            $order->setOrderStatus($this->shippingStates['open']);
        }
    }

    public function setPaymentStatus(ValueOrder $value, ShopwareOrder &$order)
    {
        if ($value->getPaid() > 0) {
            $order->setPaymentStatus($this->paymentStates['partially_paid']);
        }
        if ($value->getPaid() >= $value->getAmount()) {
            $order->setPaymentStatus($this->paymentStates['completely_paid']);
        }
        if ($value->getPaid() <= 0) {
            $order->setPaymentStatus($this->paymentStates['open']);
        }
    }

    public function getShop(int $id)
    {
        return $this->entityManager->getRepository(Shop::class)->find($id);
    }

    public function getCountries(): array
    {
        $countries = $this->entityManager->createQueryBuilder()
            ->select('countries')
            ->from(Country::class, 'countries', 'countries.iso')
            ->getQuery()
            ->getResult();

        return $countries;
    }

    public function getPaymentStates(): array
    {
        $states = $this->entityManager->createQueryBuilder()
            ->select('states')
            ->from(OrderStatus::class, 'states', 'states.name')
            ->where('states.group = :group')
            ->setParameters(array('group' => 'payment'))
            ->getQuery()
            ->getResult();

        return $states;
    }

    /**
     * @return array
     */
    public function getShippingStates(): array
    {
        $states = $this->entityManager->createQueryBuilder()
            ->select('states')
            ->from(OrderStatus::class, 'states', 'states.name')
            ->where('states.group = :group')
            ->setParameters(array('group' => 'state'))
            ->getQuery()
            ->getResult();

        return $states;
    }

    public function getDetailStates(): array
    {
        $states = $this->entityManager->createQueryBuilder()
            ->select('states')
            ->from(DetailStatus::class, 'states', 'states.id')
            ->getQuery()
            ->getResult();

        return $states;
    }

    /**
     * @return array
     */
    public function getPaymentTypes(): array
    {
        $types = $this->entityManager->createQueryBuilder()
            ->select('types')
            ->from(Payment::class, 'types', 'types.id')
            ->getQuery()
            ->getResult();

        return $types;
    }

    public function getCustomer(ValueOrder $order, ValueAddress $billingAddress, Shop $shop)
    {
        $customer = $this->entityManager->getRepository(Customer::class)
            ->findOneBy(array('email' => $billingAddress->getEmail(), 'accountMode' => 1));

        if ($customer) {
            return $customer;
        }

        return $this->createCustomer($order, $billingAddress, $shop);
    }

    public function createCustomer(ValueOrder $order, ValueAddress $billingAddress, Shop $shop)
    {
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

        try {
            $this->entityManager->flush();
        } catch (OptimisticLockException $e) {
        }

        return $customer;
    }

    public function getDefaultGroup()
    {
        return $this->entityManager->getRepository(Group::class)->findOneBy(array());
    }


}
<?php
/** @noinspection SpellCheckingInspection */

namespace viaebShopwareAfterbuy\Services\Helper;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Article\Detail as ArticleDetail;
use Shopware\Models\Attribute\Order as OrderAttributes;
use viaebShopwareAfterbuy\ValueObjects\Address as ValueAddress;
use viaebShopwareAfterbuy\ValueObjects\Order as ValueOrder;
use viaebShopwareAfterbuy\ValueObjects\Order;
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

    /** @var ShopwareArticleHelper */
    protected $articleHelper;

    /**
     * @param ShopwareArticleHelper $articleHelper
     */
    public function init(ShopwareArticleHelper $articleHelper)
    {
        $this->articleHelper = $articleHelper;
    }

    /**
     *
     */
    public function preFetch()
    {
        $this->paymentStates = $this->getPaymentStates();
        $this->shippingStates = $this->getShippingStates();
        $this->paymentTypes = $this->getPaymentTypes();
        $this->countries = $this->getCountries();
        $this->detailStates = $this->getDetailStates();
        $this->targetGroup = $this->getDefaultGroup();
    }

    /**
     * @return array
     */
    public function getABCountryCodes()
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
     * @throws ORMException
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
     * @param array $config
     * @return array
     * @throws Exception
     */
    public function getUnexportedOrders(array $config)
    {
        $query = $this->entityManager->createQueryBuilder();

        $query
            ->select(['orders'])
            ->from(ShopwareOrder::class, 'orders', 'orders.id')
            ->leftJoin('orders.attribute', 'attributes')
            ->where('attributes.afterbuyOrderId IS NULL')
            ->orWhere("attributes.afterbuyOrderId = ''")
            ->andWhere('orders.number != 0');

        if(!empty($config["minOrderDate"])) {
            $minOrderDate = new DateTime($config["minOrderDate"]);

            $query->andWhere('orders.orderTime > :minOrderDate')
                ->setParameters(array('minOrderDate' => $minOrderDate));
        }

        $orders = $query
            ->getQuery()
            ->setMaxResults(200)
            ->getResult();

        return $orders;

    }

    /**
     * @param ShopwareOrder $order
     * @return bool
     */
    public function isFullfilled(ShopwareOrder $order)
    {
        $completelyPaid = $order->getPaymentStatus()->getId() === 12;
        $completelyDelivered = $order->getOrderStatus()->getId() === 7;
        $completed = $order->getOrderStatus()->getId() === 2;

        return $completelyPaid && ($completelyDelivered || $completed);
    }

    /**
     * @return mixed
     */
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

    /**
     * @return array
     */
    public function getUnfullfilledOrders() {

        $orders = $this->entityManager->createQueryBuilder()
            ->select(['attributes.afterbuyOrderId'])
            ->from(OrderAttributes::class,
                'attributes')
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
    public function setShippingType(ShopwareOrder &$order, int $id)
    {
        $order->setDispatch($this->getShippingType($id));
    }

    /**
     * @param int $id
     *
     * @return object|Dispatch|null
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

    /**
     * @param Order $value
     * @param ShopwareOrder $order
     */
    public function setPositions(ValueOrder $value, ShopwareOrder &$order)
    {
        $details = $order->getDetails();
        $details->clear();

        foreach ($value->getPositions() as $position) {
            /**
             * @var OrderPosition $position
             */

            $detail = new Detail();

            $articleDetail = $this->articleHelper->getArticleByExternalIdentifier($position->getExternalIdentifier());

            if(!empty($articleDetail)) {
                $detail->setArticleId($articleDetail->getArticleId());
                $detail->setArticleNumber($articleDetail->getNumber());
            }
            else {
                $detail->setArticleNumber($value->getExternalIdentifier());
                $detail->setArticleId(0);
            }

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
            }            $detail->setArticleName($position->getName());

            try {
                $tax = $this->getTax($position->getTax());
            } catch (ORMException $e) {
            }

            $detail->setTaxRate($position->getTax());

            $detail->setTax($tax);
            $detail->setOrder($order);

            $details->add($detail);
        }
    }

    /**
     * @param Order $value
     * @param ShopwareOrder $order
     * @param Customer $customer
     * @param string $type
     */
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
            $order->setPayment($this->paymentTypes[0]);
        }
    }

    /**
     * @param Order $value
     * @param ShopwareOrder $order
     */
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

    /**
     * @param Order $value
     * @param ShopwareOrder $order
     * @param Shop $shop
     */
    public function setOrderMainValues(ValueOrder $value, ShopwareOrder &$order, Shop $shop)
    {
        /**
         * set main order values
         */
        $order->setInvoiceAmount($value->getAmount());
        $order->setInvoiceShipping($value->getShipping());
        if (method_exists($order, 'setInvoiceShippingTaxRate')) {
            $order->setInvoiceShippingTaxRate($value->getShippingTax());
        }
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

    /**
     * @param Order $value
     * @param ShopwareOrder $order
     */
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

    /**
     * @param int $id
     * @return object|null
     */
    public function getShop(int $id)
    {
        return $this->entityManager->getRepository(Shop::class)->find($id);
    }

    /**
     * @return mixed
     */
    public function getCountries()
    {
        $countries = $this->entityManager->createQueryBuilder()
            ->select('countries')
            ->from(Country::class, 'countries', 'countries.iso')
            ->getQuery()
            ->getResult();

        return $countries;
    }

    /**
     * @return mixed
     */
    public function getPaymentStates()
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
    public function getShippingStates()
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

    /**
     * @return mixed
     */
    public function getDetailStates()
    {
        $states = $this->entityManager->createQueryBuilder()
            ->select('states')
            ->from(DetailStatus::class, 'states', 'states.id')
            ->getQuery()
            ->getResult();

        return $states;
    }

    /**
     * Returns an indexed array of Payments
     *
     * @return array
     */
    public function getPaymentTypes()
    {
        $AB_UNI_PAYMENT = ShopwareConfigHelper::$AB_UNI_PAYMENT;
        $types = $this->entityManager->createQueryBuilder()
            ->select('types')
            ->from(Payment::class, 'types', 'types.id')
            ->addSelect("(CASE WHEN types.name = '" . $AB_UNI_PAYMENT . "' THEN 0 ELSE 1 END) AS HIDDEN mainSort")
            ->orderBy('mainSort')
            ->getQuery()
            ->getResult();

        $types = array_values($types);

        return $types;
    }

    /**
     * @param Order $order
     * @param ValueAddress $billingAddress
     * @param Shop $shop
     * @return object|Customer|null
     */
    public function getCustomer(ValueOrder $order, ValueAddress $billingAddress, Shop $shop)
    {
        $customer = $this->entityManager->getRepository(Customer::class)
            ->findOneBy(array('email' => $billingAddress->getEmail(), 'accountMode' => 1));

        if ($customer) {
            return $customer;
        }

        return $this->createCustomer($order, $billingAddress, $shop);
    }

    /**
     * @param Order $order
     * @param ValueAddress $billingAddress
     * @param Shop $shop
     * @return Customer
     */
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

        try {
            $this->entityManager->persist($customer);
        } catch (ORMException $e) {
            $this->logger->error('Error storing customer.');
        }

        try {
            $this->entityManager->persist($address);
        } catch (ORMException $e) {
            $this->logger->error('Error storing customer address.');
        }

        $customer->setDefaultBillingAddress($address);
        $customer->setDefaultShippingAddress($address);

        try {
            $this->entityManager->persist($customer);
            $this->entityManager->flush();
        } catch (OptimisticLockException $e) {
            $this->logger->error('Error writing customer data.');
        } catch (ORMException $e) {
            $this->logger->error('Error writing customer data.');
        }

        return $customer;
    }

    /**
     * @return object|null
     */
    public function getDefaultGroup()
    {
        return $this->entityManager->getRepository(Group::class)->findOneBy(array());
    }

    /**
     * @param ShopwareOrder[] $orders
     * @return ShopwareOrder[]
     */
    public function addAfterbuyOrderIdToOrders($orders)
    {
        foreach ($orders['data'] as $index => $order) {
            /** @var ShopwareOrder $currentOrder */
            $currentOrder = $this->entityManager->getRepository(ShopwareOrder::class)->find($order['id']);
            $orders['data'][$index]['afterbuyOrderId'] = $currentOrder->getAttribute()->getAfterbuyOrderId();
        }

        return $orders;
    }

    /**
     *
     * @param Order[] $orders
     */
    public function resetArticleChangeTime(array $orders)
    {
        foreach ($orders as $order) {
            /** @var OrderPosition $position */
            foreach ($order->getPositions() as $position) {
                $externalIdentifier = $position->getExternalIdentifier();

                /** @var ArticleDetail $detail */
                $detail = $this->articleHelper->getArticleByExternalIdentifier($externalIdentifier);

                if(empty($detail)) {
                    continue;
                }

                try {
                    $this->entityManager->persist($detail);
                } catch (ORMException $e) {
                    $this->logger->error($e->getMessage());
                }
            }
        }

        try {
            $this->entityManager->flush();
        } catch (ORMException $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * @param Billing|Shipping|ModelEntity $entity
     * @return ValueAddress
     */
    public function buildAddress(ModelEntity $entity) {
        $address = new ValueAddress();
        $address->setFirstname($entity->getFirstName());
        $address->setLastname($entity->getLastName());
        $address->setCompany($entity->getCompany());
        $address->setStreet($entity->getStreet());

        if($entity->getAdditionalAddressLine1()) {
            $address->setAdditionalAddressLine1($entity->getAdditionalAddressLine1());
        }
        $address->setZipcode($entity->getZipCode());
        $address->setCity($entity->getCity());
        $address->setCountry($entity->getCountry()->getIso());
        $address->setPhone($entity->getPhone());

        return $address;
    }

    /**
     * @param ShopwareOrder|ModelEntity $entity
     * @return ArrayCollection
     */
    public function buildPositions(ShopwareOrder $entity) {
        $positions = new ArrayCollection();

        foreach($entity->getDetails() as $position) {
            /**
             * @var Detail $position
             */
            $orderPosition = new OrderPosition();
            if($position->getEan()) {
                $orderPosition->setExternalIdentifier($position->getEan());
            }

            $orderPosition->setInternalIdentifier($position->getArticleNumber());
            $orderPosition->setName($position->getArticleName());
            $orderPosition->setPrice($position->getPrice());
            $orderPosition->setQuantity($position->getQuantity());
            $orderPosition->setTax($position->getTaxRate());

            $positions->add($orderPosition);
        }

        return $positions;
    }

    /**
     * @param Order $order
     * @param ShopwareOrder $entity
     */
    public function setOrderValues(Order &$order, ShopwareOrder $entity) {
        /** @noinspection PhpParamsInspection */
        $order->setCreateDate($entity->getOrderTime());
        $order->setShipping($entity->getInvoiceShipping());

        try {
            $order->setShippingType($entity->getDispatch()->getName());
        }
        catch(Exception $e) {
            $order->setShippingType('Standard');
        }

        $order->setPaymentType($entity->getPayment()->getName());
        $order->setPaymentTypeId($entity->getPayment()->getId());

        if($entity->getTaxFree()) {
            $order->setTaxFree(true);
        }
        $order->setInternalIdentifier($entity->getNumber());
        $order->setCurrency($entity->getCurrency());
        $order->setTransactionId($entity->getTransactionId());
    }

    /**
     * @param Order $order
     * @param ShopwareOrder $entity
     */
    public function setOrderStatus(Order &$order, ShopwareOrder $entity) {
        if($entity->getPaymentStatus()->getId() === 12) {
            $order->setPaid(true);
            $order->setCleared(true);
        }
    }

    /**
     * @param Order $order
     * @param ValueAddress $billingAddress
     * @param ShopwareOrder $entity
     */
    public function setOrderCustomerData(Order &$order, ValueAddress &$billingAddress, ShopwareOrder $entity) {
        if ($entity->getCustomer()) {
            $billingAddress->setEmail($entity->getCustomer()->getEmail());
            $order->setCustomerNumber($entity->getCustomer()->getNumber());
        }

        if ($entity->getCustomer() && $entity->getCustomer()->getBirthday()) {
            /** @noinspection PhpParamsInspection */
            $billingAddress->setBirthday($entity->getCustomer()->getBirthday());
        }
    }
}
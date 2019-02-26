<?php

namespace viaebShopwareAfterbuy\ValueObjects;

class OrderStatus extends AbstractValueObject {

    /**
     * @var string
     */
    protected $afterbuyOrderId;

    /**
     * @var \DateTime
     */
    protected $paymentDate;

    /**
     * @var \DateTime
     */
    protected $shippingDate;

    /**
     * @var float
     */
    protected $amount;

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount(float $amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getAfterbuyOrderId()
    {
        return $this->afterbuyOrderId;
    }

    /**
     * @param string $afterbuyOrderId
     */
    public function setAfterbuyOrderId(string $afterbuyOrderId)
    {
        $this->afterbuyOrderId = $afterbuyOrderId;
    }

    /**
     * @return \DateTime
     */
    public function getPaymentDate()
    {
        return $this->paymentDate;
    }

    /**
     * @param \DateTime $paymentDate
     */
    public function setPaymentDate(\DateTime $paymentDate)
    {
        $this->paymentDate = $paymentDate;
    }

    /**
     * @return \DateTime
     */
    public function getShippingDate()
    {
        return $this->shippingDate;
    }

    /**
     * @param \DateTime $shippingDate
     */
    public function setShippingDate(\DateTime $shippingDate)
    {
        $this->shippingDate = $shippingDate;
    }


}
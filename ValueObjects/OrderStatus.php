<?php

namespace FatchipAfterbuy\ValueObjects;

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
     * @return string
     */
    public function getAfterbuyOrderId(): string
    {
        return $this->afterbuyOrderId;
    }

    /**
     * @param string $afterbuyOrderId
     */
    public function setAfterbuyOrderId(string $afterbuyOrderId): void
    {
        $this->afterbuyOrderId = $afterbuyOrderId;
    }

    /**
     * @return \DateTime
     */
    public function getPaymentDate(): \DateTime
    {
        return $this->paymentDate;
    }

    /**
     * @param \DateTime $paymentDate
     */
    public function setPaymentDate(\DateTime $paymentDate): void
    {
        $this->paymentDate = $paymentDate;
    }

    /**
     * @return \DateTime
     */
    public function getShippingDate(): \DateTime
    {
        return $this->shippingDate;
    }

    /**
     * @param \DateTime $shippingDate
     */
    public function setShippingDate(\DateTime $shippingDate): void
    {
        $this->shippingDate = $shippingDate;
    }


}
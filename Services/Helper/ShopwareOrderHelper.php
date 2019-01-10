<?php

namespace FatchipAfterbuy\Services\Helper;

use FatchipAfterbuy\Components\Helper;
use Shopware\Components\Model\ModelEntity;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Category\Category;
use Shopware\Models\Tax\Tax;

class ShopwareOrderHelper extends AbstractHelper {

    protected $taxes;

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


}
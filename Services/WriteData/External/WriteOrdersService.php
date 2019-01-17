<?php

namespace FatchipAfterbuy\Services\WriteData\External;

use FatchipAfterbuy\Services\Helper\AbstractHelper;
use FatchipAfterbuy\Services\Helper\ShopwareCategoryHelper;
use FatchipAfterbuy\Services\Helper\ShopwareOrderHelper;
use FatchipAfterbuy\Services\WriteData\AbstractWriteDataService;
use FatchipAfterbuy\Services\WriteData\WriteDataInterface;
use FatchipAfterbuy\ValueObjects\Category;
use FatchipAfterbuy\ValueObjects\Order;
use FatchipAfterbuy\ValueObjects\OrderPosition;

class WriteOrdersService extends AbstractWriteDataService implements WriteDataInterface {

    protected $ABCountries;
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

        $this->logger->info("Storing " . count($data) . " items.", array("Categories", "Write", "Internal"));

        //TODO: url generation for send into library
        //TODO: url encode values
        //TODO: convert float to ,values

        $orders = [];

        /**
         * @var ShopwareOrderHelper $helper
         */
        $helper = $this->helper;

        $this->ABCountries = $helper->getABCountryCodes();
        
        foreach($data as $value) {
            /**
             * @var Order $value
             */

            $orders[$value->getInternalIdentifier()] = array(
                'PosAnz' => $value->getPositions()->count(),
                'Kbenutzername' => $value->getCustomerNumber(),
                'KFirma' => $value->getBillingAddress()->getCompany(),
                'KVorname' => $value->getBillingAddress()->getFirstname(),
                'KNachname' => $value->getBillingAddress()->getLastname(),
                'KStrasse' => $value->getBillingAddress()->getStreet(),
                'KStrasse2' => $value->getBillingAddress()->getAdditionalAddressLine1(),
                'KPLZ' => $value->getBillingAddress()->getZipcode(),
                'KOrt' => $value->getBillingAddress()->getCity(),
                'KLand' => $this->ABCountries[$value->getBillingAddress()->getCountry()],

                'KLFirma' => $value->getShippingAddress()->getCompany(),
                'KLVorname' => $value->getShippingAddress()->getFirstname(),
                'KLNachname' => $value->getShippingAddress()->getLastname(),
                'KLStrasse' => $value->getShippingAddress()->getStreet(),
                'KLStrasse2' => $value->getShippingAddress()->getAdditionalAddressLine1(),
                'KLPLZ' => $value->getShippingAddress()->getZipcode(),
                'KLOrt' => $value->getShippingAddress()->getCity(),
                'KLLand' => $this->ABCountries[$value->getShippingAddress()->getCountry()],
                
                'Ktelefon' => $value->getBillingAddress()->getPhone(),
                'Kemail' => $value->getBillingAddress()->getEmail(),
                //TODO: format date
                'KBirthday' => $value->getBillingAddress()->getBirthday(),
                'BuyDate' => $value->getCreateDate(),

                'Versandart' => $value->getShippingType(),

                //TODO: format value
                'Versandkosten' => $value->getShipping(),

                //TODO: parse, may use ZFunktionsID
                'Zahlart' => $value->getPaymentType(),

                //TODO: config
                'NoFeedback' => 0,

                'NoVersandCalc' => 1,
                'Versandgruppe' => 'shop',

                'MwStNichtAusweisen' => ($value->isTaxFree()) ? 1 : 0,
                'EkundenNr' => $value->getCustomerNumber(),
                'Kundenerkennung' => 1,
                'NoeBayNameAktu' => 1,
                'Artikelerkennung' => 13,
                'VID' => $value->getInternalIdentifier(),
                'SoldCurrency' => $value->getCurrency(),
                'SetPay' => ($value->isCleared()) ? 1 : 0,
                'CheckVID' => 1,
                'CheckPackstation' => 1,
                'PaymentTransactionId' => $value->getTransactionId()
            );

            $i = 1;

            foreach ($value->getPositions() as $position) {
                /**
                 * @var OrderPosition $position
                 */

                //TODO: set numbers
                $orders[$value->getInternalIdentifier()]['Artikelnr_' . $i] = '';
                $orders[$value->getInternalIdentifier()]['Artikelnr1_' . $i] = '';
                $orders[$value->getInternalIdentifier()]['Artikelnr2_' . $i] = '';

                $orders[$value->getInternalIdentifier()]['Artikelname_' . $i] = $position->getName();
                //TODO: format price, tax
                $orders[$value->getInternalIdentifier()]['ArtikelEpreis_' . $i] = $position->getPrice();
                $orders[$value->getInternalIdentifier()]['ArtikelMwSt_' . $i] = $position->getTax();
                $orders[$value->getInternalIdentifier()]['ArtikelMenge_' . $i] = $position->getQuantity();



                $i++;
            }
        }

        return $orders;
    }


    /**
     * @param $targetData
     * @return mixed|void
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function send($targetData) {
        //TODO: update attribute for submission
    }
}
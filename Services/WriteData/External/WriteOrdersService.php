<?php

namespace viaebShopwareAfterbuy\Services\WriteData\External;

use Fatchip\Afterbuy\ApiClient;
use viaebShopwareAfterbuy\Components\Helper;
use viaebShopwareAfterbuy\Services\Helper\ShopwareOrderHelper;
use viaebShopwareAfterbuy\Services\WriteData\AbstractWriteDataService;
use viaebShopwareAfterbuy\Services\WriteData\WriteDataInterface;
use viaebShopwareAfterbuy\ValueObjects\Order;
use viaebShopwareAfterbuy\ValueObjects\OrderPosition;

class WriteOrdersService extends AbstractWriteDataService implements WriteDataInterface {

    protected $ABCountries;

    /** @var ShopwareOrderHelper $helper */
    public $helper;

    /**
     * @param array $data
     * @return mixed|void
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

        $this->logger->debug("Storing " . count($data) . " items.", array($data));

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

                'KBirthday' => ($value->getBillingAddress()->getBirthday()) ? date_format($value->getBillingAddress()->getBirthday(), 'd.m.Y H:i:s') : '',
                'BuyDate' => date_format($value->getCreateDate(), 'd.m.Y H:i:s'),

                'Versandart' => $value->getShippingType(),

                'Versandkosten' => Helper::convertNumberToABString($value->getShipping()),

                'Zahlart' => $value->getPaymentType(),

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

                $orders[$value->getInternalIdentifier()]['Artikelnr_' . $i] = $position->getInternalIdentifier();
                $orders[$value->getInternalIdentifier()]['Artikelnr1_' . $i] = $position->getExternalIdentifier();
                $orders[$value->getInternalIdentifier()]['ArtikelStammID_' . $i] = $position->getInternalIdentifier();

                $orders[$value->getInternalIdentifier()]['Artikelname_' . $i] = $position->getName();

                $orders[$value->getInternalIdentifier()]['ArtikelEpreis_' . $i] = Helper::convertNumberToABString($position->getPrice());
                $orders[$value->getInternalIdentifier()]['ArtikelMwSt_' . $i] = Helper::convertNumberToABString($position->getTax());

                $orders[$value->getInternalIdentifier()]['ArtikelMenge_' . $i] = $position->getQuantity();

                $i++;
            }
        }

        return $orders;
    }


    /**
     * @param $targetData
     * @return array
     */
    public function send($targetData) {
        $api = new ApiClient($this->apiConfig, $this->logger);

        $submitted = [];

        foreach ($targetData as $order) {
            $response = $api->sendOrdersToAfterbuy($order);

            if(empty($response)) {
                continue;
            }

            if(array_key_exists('ordernumber', $response)) {
                $submitted[$response['ordernumber']] = $response['afterbuyId'];
            }

            if(array_key_exists('error', $response)) {
                $this->logger->error('Error submitting order', array($response));
            }
        }

        try {
            $this->helper->setAfterBuyIds($submitted);
        }
        catch(\Exception $e) {
            $this->logger->error('Error storing external order ids', $e->getMessage());
        }

        return $submitted;

    }
}
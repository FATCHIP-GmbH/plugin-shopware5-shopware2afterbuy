<?php
/** @noinspection SpellCheckingInspection */

namespace viaebShopwareAfterbuy\Services\WriteData\External;

use Exception;
use Fatchip\Afterbuy\ApiClient;
use viaebShopwareAfterbuy\Components\Helper;
use viaebShopwareAfterbuy\Services\Helper\ShopwareOrderHelper;
use viaebShopwareAfterbuy\Services\WriteData\AbstractWriteDataService;
use viaebShopwareAfterbuy\Services\WriteData\WriteDataInterface;
use viaebShopwareAfterbuy\ValueObjects\Order;
use viaebShopwareAfterbuy\ValueObjects\OrderPosition;

/**
 * Class WriteOrdersService
 * @package viaebShopwareAfterbuy\Services\WriteData\External
 * @property ShopwareOrderHelper $helper
 */
class WriteOrdersService extends AbstractWriteDataService implements WriteDataInterface {

    protected $ABCountries;

    /**
     * @param array $data
     * @return array|null
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
     * @return array
     */
    public function transform(array $data) {

        $this->logger->debug('Storing ' . count($data) . ' items.', array($data));

        $orders = [];

        $this->ABCountries = $this->helper->getABCountryCodes();
        
        foreach($data as $value) {
            /**
             * @var Order $value
             */

            if($value->getShippingAddress() === null) {
                continue;
            }

            $internalIdentifyer = $value->getInternalIdentifier();

            /** @noinspection PhpNonStrictObjectEqualityInspection */
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

                'Lieferanschrift' => ($value->getBillingAddress()->compare($value->getShippingAddress())) ? 0 : 1,

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

                'KBirthday' => $value->getBillingAddress()->getBirthday() ? date_format($value->getBillingAddress()->getBirthday(), 'd.m.Y H:i:s') : '',
                'BuyDate' => date_format($value->getCreateDate(), 'd.m.Y H:i:s'),

                'Versandart' => $value->getShippingType(),

                'Versandkosten' => Helper::convertNumberToABString($value->getShipping()),

                'Zahlart' => $value->getPaymentType(),

                'NoFeedback' => 0,

                'NoVersandCalc' => 1,
                'Versandgruppe' => 'shop',

                'MwStNichtAusweisen' => $value->isTaxFree() ? 1 : 0,
                'EkundenNr' => $value->getCustomerNumber(),
                'Kundenerkennung' => 1,
                'NoeBayNameAktu' => 1,
                'Artikelerkennung' => ((int)$this->config['ordernumberMapping'] === 0) ? 0 : 1,
                'VID' => $internalIdentifyer,
                'SoldCurrency' => $value->getCurrency(),
                'SetPay' => $value->isCleared() ? 1 : 0,
                'CheckVID' => 1,
                'CheckPackstation' => 1,
                'PaymentStatus' => $value->getPaymentStatus(),
                'PaymentTransactionId' => $value->getTransactionId(),
                'AdditionalInfo' => $value->getTrackingNumber()
            );

            $i = 1;

            foreach ($value->getPositions() as $position) {
                /**
                 * @var OrderPosition $position
                 */

                $mainNumber = $position->getExternalIdentifier();
                if(!is_numeric($mainNumber)) {
                    $mainNumber = preg_replace('~\D~', '', $position->getInternalIdentifier());
                }

				/* ************************************************
				$directShopLink 
				0 default (old / default behavior up to Aferbuy Connector v.1.0.3)
				1 for direct link with article number in afterbuy auction overview
				
				ToDo:
				- include variable to plugin config screen or modify sourcecode (fixed) if old method / value is not needed anymore.
				- find a better (more secure) way to read the shop URL. Maybe from Shopware config and optional with subfolder, if shop is not in the root directory.
				- optional - not needed but maybe a little bit better - replace the whole article_link with the exact string read and generated from the article directly. - maybe a more optimized way.
				************************************************ */
				$directShopLink = 1;

				if ($directShopLink == 1)
					// $mainNumber = $position->getInternalIdentifier();				// Shopware article number - removed 2020-12-09 because if not numeric, AB-Response is not successful. Need to be numeric value for afterbuy.
					$mainNumber = preg_replace('/[^0-9]/', '', $position->getInternalIdentifier());	// Shopware article number (with replaces all which is not numeric)
                
                if(empty($mainNumber)) {
                    $mainNumber = 0;
                }

                $orders[$internalIdentifyer]['Artikelnr_' . $i] = $mainNumber;
				if ($directShopLink == 1) {
					$orders[$internalIdentifyer]['Artikelnr1_' . $i] = $position->getInternalIdentifier();										// Shopware article number
					$websiteUrl = 'https://' . $_SERVER['SERVER_NAME'];																			// read Shop URL - better replace with shopURL from Shopware config...
					$orders[$internalIdentifyer]['ArtikelLink_' . $i] = $websiteUrl . '/search?sSearch=' . $position->getInternalIdentifier();	// direct Link to the shop article
				} else {
					$orders[$internalIdentifyer]['Artikelnr1_' . $i] = $position->getExternalIdentifier();										// Shopware EAN (old method / value)
				}

                $orders[$internalIdentifyer]['ArtikelStammID_' . $i] = $position->getInternalIdentifier();

                $orders[$internalIdentifyer]['Artikelname_' . $i] = $position->getName();

                $orders[$internalIdentifyer]['ArtikelEpreis_' . $i] = Helper::convertNumberToABString($position->getPrice());
                $orders[$internalIdentifyer]['ArtikelMwSt_' . $i] = Helper::convertNumberToABString($position->getTax());

                $orders[$internalIdentifyer]['ArtikelMenge_' . $i] = $position->getQuantity();

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
        catch(Exception $e) {
            $this->logger->error('Error storing external order ids', array($e->getMessage()));
        }

        return $submitted;

    }
}

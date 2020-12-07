<?php /** @noinspection PhpUnused */

/* *******************************************************************************************
Comment from XXL-Webdesign (2020-12-07):
This file is directly copied from the original file of the Shopware Afterbuy Connector plugin v 1.0.3
[Shoproot]/custom/plugins/viaebShopwareAfterbuy/Library/API/ApiClient.php
because was removed or could not be edited anymore on this GitHub Project. (Can not create a fork of it, other files are working...)
If you download the project, Library/API directory is empty.
So please reupload these files to this project in the future.

Modification is done as described in the afterbuy forum thread:
https://forum.afterbuy.de/beitraege.aspx?foreninhalteID=70541

Only the part after "$this->logger->debug('Request', array($request, $response)); }" was inserted (and this comment)
******************************************************************************************* */

namespace Fatchip\Afterbuy;

use Monolog\Logger;
use RuntimeException;
use Symfony\Component\Serializer\Serializer;

class ApiClient
{
    /**
     * AfterbuyClient constructor.
     *
     * Pass your configuration parameters as an array like this:
     * ```
     * $config = array(
     *      'afterbuyAbiUrl' => <AfterbuyAbiUrl>,
     *      'afterbuyShopInterfaceBaseUrl' => <AfterbuyShopInterfaceBaseUrl>,
     *      'afterbuyPartnerId' => <AfterbuyPartnerId>,
     *      'afterbuyPartnerPassword' => <AfterbuyPartnerPassword>,
     *      'afterbuyUsername' => <AfterbuyUsername>,
     *      'afterbuyUserPassword' => <AfterbuyUserPassword>,
     *      'logLevel' => <LogLevel>,
     * );
     * ```
     * @param array $config
     * @param null $logger
     */
    public function __construct($config, $logger = null)
    {
        if (!class_exists(Serializer::class)) {
            /** @noinspection PhpIncludeInspection */
            require __DIR__ . '/vendor/autoload.php';
        }
        foreach ($config as $key => $value) {
            $this->$key = $value;
        }
        $encoders = [new Encoder()];
        $normalizers = [new Normalizer()];
        $this->serializer = new Serializer($normalizers, $encoders);
        $this->logger = $logger;
    }

    public function getAfterbuyTime()
    {
        $request = $this->buildRequest('getAfterbuyTime', array());
        $response = $this->sendRequest($request);

        return $response;
    }


    public function updateShopProducts($products)
    {
        $request = $this->buildRequest('UpdateShopProducts', $products);
        $response = $this->sendRequest($request);
        if ($response['CallStatus'] === 'Error') {
            if (array_key_exists('ErrorCode', $response['Result']['ErrorList']['Error'])) {
                throw new RuntimeException(
                    $response['Result']['ErrorList']['Error']['ErrorDescription'],
                    $response['Result']['ErrorList']['Error']['ErrorCode']
                );
            }

            throw new RuntimeException('An unknown error occured during API communication.');
        }
        return $response;
    }

    public function getCatalogsFromAfterbuy(
        $maxCatalogs,
        $detailLevel,
        $pageNumber,
        $dataFilter = []
    ) {
        $params = [
            'MaxCatalogs' => $maxCatalogs,
            'PageNumber'  => $pageNumber,
            'DataFilter'      => $dataFilter,
        ];

        $request = $this->buildRequest('GetShopCatalogs', $params, false, 'EN', $detailLevel);
        return $this->sendRequest($request);
    }

    public function updateOrderStatus(array $content)
    {
        $request = $this->buildRequest('UpdateSoldItems', $content);
        return $this->sendRequest($request);
    }

    public function updateCatalogs($catalogs)
    {
        $params = [
            'Catalogs' => [
                'Catalog' => $catalogs,
                'UpdateAction' => 2,
            ]
        ];

        $request = $this->buildRequest('UpdateCatalogs', $params);

        return $this->sendRequest($request);
    }

    public function getOrdersFromAfterbuy($dataFilter = [], $detailLevel = 0, $iMaxShopItems = 250, $iPage = 0)
    {
        $params = [
            'MaxSoldItems'                   => $iMaxShopItems,
            'SuppressBaseProductRelatedData' => 0,
            'PaginationEnabled'              => 1,
            'PageNumber'                     => $iPage,
            'ReturnShop20Container'          => 0,
            'DataFilter'                     => $dataFilter
        ];

        $request = $this->buildRequest('GetSoldItems', $params, false, 'EN', $detailLevel);
        return $this->sendRequest($request);
    }

    public function getAllShopProductsFromAfterbuy(
        $dataFilter = []
    ) {
        $i = 1;

        $articles = [];

        do {
            $response = $this->getShopProductsFromAfterbuy($dataFilter, 250, $i++);

            if (!array_key_exists('Products', $response['Result'])) {
                break;
            }

            if (array_key_exists('ProductID', $response['Result']['Products']['Product'])) {
                $articles[] = $response['Result']['Products']['Product'];
            } else {
                foreach ($response['Result']['Products']['Product'] as $product) {
                    $articles[] = $product;
                }
            }
        } while ($response['Result']['HasMoreProducts'] == '1');

        return $articles;
    }

    /**
     * Returns the Afterbuy products as array
     *
     * @param int   $iMaxShopItems
     * @param int   $iPage
     * @param array $dataFilter DataFilter, as described in
     *                          https://xmldoku.afterbuy.de/dokued/
     *                          GetShopProducts
     *
     * @return array
     */
    public function getShopProductsFromAfterbuy(
        $dataFilter = [],
        $iMaxShopItems = 250,
        $iPage = 0
    ) {
        $params = [
            'MaxShopItems'                   => $iMaxShopItems,
            'SuppressBaseProductRelatedData' => 0,
            'PaginationEnabled'              => 1,
            'PageNumber'                     => $iPage,
            'ReturnShop20Container'          => 0,
            'DataFilter'                     => $dataFilter
        ];
        $request = $this->buildRequest('GetShopProducts', $params);
        return $this->sendRequest($request);
    }

    /**
     * @param string $callName
     * @param array $content
     * @param bool $shopInterface
     * @param string $errorLanguage
     * @param int $detailLevel
     * @return array
     */
    protected function buildRequest($callName, $content, $shopInterface = false, $errorLanguage = 'EN', $detailLevel = 0)
    {
        $params = [
            'PartnerID' => $this->afterbuyPartnerId,
            'UserID' => $this->afterbuyUsername,
            'UserPassword' => $this->afterbuyUserPassword
        ];

        if ($shopInterface) {
            $params['Action'] = $callName;
            $params['PartnerPass'] = $this->afterbuyPartnerPassword;

            $request = array_merge($params, $content);
            return http_build_query($request);
        }

        $params['ErrorLanguage'] = $errorLanguage;
        $params['CallName'] = $callName;
        $params['DetailLevel'] = $detailLevel;
        $params['PartnerPassword'] = $this->afterbuyPartnerPassword;

        $request = array_merge_recursive(['AfterbuyGlobal' => $params], $content);
        return $this->serializer->normalize($request);
    }

    /**
     * @param $values
     * @return mixed
     */
    public function sendOrdersToAfterbuy($values)
    {
        $result = array();

        /** @var array $values */
        $request = $this->buildRequest('new', $values, true);
        $response = $this->sendRequest($request, true);

        if ($response['success'] == '1') {
            $result = array(
                'ordernumber' => $response['data']['VID'],
                'afterbuyId' => $response['data']['AID']
            );
        } elseif (array_key_exists('errorlist', $response)) {
            $result = array('error' => $response['errorlist']);
        }

        return $result;
    }

    /**
     * @param mixed $request
     * @param bool $shopInterface
     * @return mixed
     */
    protected function sendRequest($request, $shopInterface = false)
    {
        if (!$shopInterface) {
            $request = $this->serializer->encode($request, 'request/xml');
            $resource = $this->afterbuyAbiUrl;
        } else {
            $resource = $this->afterbuyShopInterfaceBaseUrl;
        }

        $ch = curl_init($resource);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);

        if (!$shopInterface) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        }

        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);

        if ($this->logger instanceof Logger) {
            $this->logger->debug('Request', array($request, $response));
        }

	// BEGIN Advanced LogLevel  // added 2020-12-07 by XXL-Webdesgin
	/*
	Das erweiterte Debug-Logging wird über das Shopware Backend Log angezeigt (Shopware-Backend - Einstellungen - Logfile - Systemlog - aferbuy_production-[Datum].log)
	Debuglevel:
	0 - Alle Debug-Meldungen komplett deaktiviert.
	1 - (Standard) nur Standard-Alive-Meldungen (werden angezeigt als Level "Error", Meldung "No Data recived" und Content "Orders, Read, Internal" -- aktuell ist es komplett deaktiviert / auskommentiert in der ReadOrdersService.php, da Variable in dieser Datei nicht übergeben werden kann)
	2 - Standard-Alive Meldungen deaktiviert, Logging bei Fehlern (nur wenn der Aferbuy Response Success = 0 bzw. der Call-Status nicht successful ist)
	3 - (Empfohlen) Standard-Alive Meldungen deaktiviert, Logging bei Fehlern, sowie Logging von Bestellübergaben an Aferbuy
	4 - Standard-Alive Meldungen deaktiviert, Logging bei Fehlern, Logging von Bestellübergaben an Aferbuy sowie erweiterte Alive-Meldungen, dass der CronJob aktiv ist

	Schöner wäre noch ein zusätzliches Parameterfeld ("Erweitertes Logging-Level" --> $advLogLevel) in der Plugin Konfiguration ("Verbesserungsvorschlag")
	Dann könnte der User das Loglevel bequem über die Konfiguraton ändern.

	Hinweis: Folgende Datei wurde modifiziert, um das alte Logging zu deaktivieren:
	[Shopware-Root]/custom/plugins/viaebShopwareAfterbuy/Services/ReadData/Internal/ReadOrdersService.php
	(in v1.0.3 Zeile 89 auskommentiert)

	*/

	$advLogLevel = 3;

	if ($advLogLevel >= 1) {
		$requestFiltered = preg_replace('/<UserPassword>(.*?)<\/UserPassword>/', '<UserPassword>XXXXXXXX</UserPassword>', $request);						// remove Passwords
		$requestFiltered = preg_replace('/<PartnerPassword>(.*?)<\/PartnerPassword>/', '<PartnerPassword>XXXXXXXX</PartnerPassword>', $requestFiltered);	// remove Passwords 
		$requestFiltered = preg_replace('/&UserPassword=(.*?)&Action=/', '&UserPassword=XXXXXXXXXX&Action=', $requestFiltered);								// remove Passwords 
		$requestFiltered = preg_replace('/&PartnerPass=(.*?)&PosAnz=/', '&PartnerPass=XXXXXXXX&PosAnz=', $requestFiltered);									// remove Passwords 
		$requestFiltered = str_replace(array("\r", "\n", "\t"), '', $requestFiltered);		// removes \n \r \t
		$requestFiltered = preg_replace("/[\s][\s]*/", " ", $requestFiltered);				// removes double Whitespaces
		$responseFiltered = str_replace(array("\r", "\n", "\t"), '', $response);			// removes \n \r \t
		$responseFiltered = preg_replace("/[\s][\s]*/", " ", $responseFiltered);			// removes double Whitespaces

		$statusText1 = '';
		$statusText2 = 'Request and Response (XML)';
		$content = array($requestFiltered, $responseFiltered, 'Shop-Doku (Parameter): https://xmldoku.afterbuy.de/shopdoku/', 'XML Doku: https://xmldoku.afterbuy.de/dokued/');

		$xmlResponse = simplexml_load_string($responseFiltered);
		if (($xmlResponse->CallStatus == "Success") OR ($xmlResponse->success == "1")) {
			$needle = "&ArtikelStammID_1=";
			if(strpos($requestFiltered, $needle) !== false){			// wenn der Request String "&ArtikelStammID=" enthält, handelt es sich um die Übergabe einer Bestellung.
				if ($advLogLevel >= 3) {
					$statusText1 = 'no Error - only info - Exported offer successfully to Afterbuy - ';
					$this->logger->error($statusText1 . $statusText2, $content);
				}
			} else{
				if ($advLogLevel >= 4) {
					$statusText1 = 'no Error - only info - CronJob working correctly - ';
					$this->logger->error($statusText1 . $statusText2, $content);
				}
			}
		} else {														// if no Success in AB-Response...
			if ($advLogLevel >= 2) {
				$statusText1 = 'ERROR! Check ';
				$this->logger->error($statusText1 . $statusText2, $content);
			}
		}
	}
	// END Advanced Log

        return $this->serializer->decode($response, 'response/xml');
    }

    /**
     * Error log level 1 = Only errors, 2 = Errors and warnings, 3 = Output all
     * @var int
     */
    protected $logLevel = 1;

    /**
     * Filename for logfile
     * @var string
     */
    protected $afterbuyLogFilepath;

    /**
     * ABI URL of Afterbuy
     * http://api.afterbuy.de/afterbuy/ABInterface.aspx
     * @var string
     */
    protected $afterbuyAbiUrl = '';

    /**
     * Shop Interface Base URL of Afterbuy
     * https://www.afterbuy.de/afterbuy/ShopInterface.aspx
     * @var string
     */
    protected $afterbuyShopInterfaceBaseUrl = '';

    /**
     * Partner ID for Afterbuy
     * @var string
     */
    protected $afterbuyPartnerId = '';

    /**
     * Partner password for Afterbuy
     * @var string
     */
    protected $afterbuyPartnerPassword = '';

    /**
     * User name for Afterbuy
     * @var string
     */
    protected $afterbuyUsername = '';

    /**
     * User password for Afterbuy
     * @var string
     */
    protected $afterbuyUserPassword = '';

    /**
     * Serializer for API communication
     * @var Serializer
     */
    protected $serializer;

    protected $logger;
}

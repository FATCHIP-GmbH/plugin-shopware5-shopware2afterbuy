<?php

namespace Fatchip\Afterbuy;

use Fatchip\Afterbuy\Types\Product;
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
     */
    public function __construct($config)
    {
        if (!class_exists('\\Symfony\\Component\\Serializer\\Serializer')) {
            require __DIR__ . '/vendor/autoload.php';
        }
        foreach ($config as $key => $value) {
            $this->$key = $value;
        }
        $encoders = [new Encoder()];
        $normalizers = [new Normalizer()];
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    /**
     * @param Product[]|Product $products
     * @return mixed
     * @throws \Exception
     */
    public function updateShopProducts($products)
    {
        if (!is_array($products)) {
            $products = [$products];
        }
        foreach ($products as $index => $value) {
            if ($value instanceof Product === false) {
                throw new \InvalidArgumentException("Item '{$index}' is " .
                    "not an instance of \Fatchip\Afterbuy\Types\Product");
            }
        }
        $request = $this->buildRequest('UpdateShopProducts', ['Products' => ['Product' => $products]]);
        $response = $this->sendRequest($request);
        if ($response['CallStatus'] !== 'Success') {
            if ($response['Result']['ErrorList']['Error']['ErrorCode']) {
                throw new \Exception(
                    $response['Result']['ErrorList']['Error']['ErrorDescription'],
                    $response['Result']['ErrorList']['Error']['ErrorCode']
                );
            } else {
                throw new \Exception("An unknown error occured during API communication.");
            }
        }
        return $response;
    }

    /**
     * @param string $callName
     * @param array $content
     * @param string $errorLanguage
     * @param int $detailLevel
     * @return array
     */
    protected function buildRequest($callName, $content, $errorLanguage = 'EN', $detailLevel = 0)
    {
        $params = [
            'PartnerID' => $this->afterbuyPartnerId,
            'PartnerPassword' => $this->afterbuyPartnerPassword,
            'UserID' => $this->afterbuyUsername,
            'UserPassword' => $this->afterbuyUserPassword,
            'ErrorLanguage' => $errorLanguage,
            'CallName' => $callName,
            'DetailLevel' => $detailLevel,
        ];
        $request = array_merge_recursive(['AfterbuyGlobal' => $params], $content);
        return $this->serializer->normalize($request);
    }

    /**
     * @param mixed $request
     * @return mixed
     */
    protected function sendRequest($request)
    {
        $request = $this->serializer->encode($request, 'request/xml');
        $ch = curl_init($this->afterbuyAbiUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
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
    protected $afterbuyLogFilepath = null;

    /**
     * ABI URL of Afterbuy
     * http://api.afterbuy.de/afterbuy/ABInterface.aspx
     * @var string
     */
    protected $afterbuyAbiUrl = "";

    /**
     * Shop Interface Base URL of Afterbuy
     * https://www.afterbuy.de/afterbuy/ShopInterface.aspx
     * @var string
     */
    protected $afterbuyShopInterfaceBaseUrl = "";

    /**
     * Partner ID for Afterbuy
     * @var string
     */
    protected $afterbuyPartnerId = "";

    /**
     * Partner password for Afterbuy
     * @var string
     */
    protected $afterbuyPartnerPassword = "";

    /**
     * User name for Afterbuy
     * @var string
     */
    protected $afterbuyUsername = "";

    /**
     * User password for Afterbuy
     * @var string
     */
    protected $afterbuyUserPassword = "";

    /**
     * Serializer for API communication
     * @var Serializer
     */
    protected $serializer = null;
}

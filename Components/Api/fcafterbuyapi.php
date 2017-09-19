<?php

// ToDo: find a way to remove namespace??
namespace Shopware\FatchipShopware2Afterbuy\Components\Api;

/**
 * @see Afterbuy API documentation http://xmldoku.afterbuy.de/dokued/
 */

/**
 * fcafterbuy core class
 *
 * @author andre
 */
class fcafterbuyapi {

    /**
     * Error log level 1=Only errors, 2= Errors and warnings, 3=Output all
     * @var int
     */
    protected $_iFcLogLevel;

    /**
     * Filename for logfile
     * @var string
     */
    protected $_sFcAfterbuyLogFilepath = null;

    /**
     * ShopInterface Base URL of Afterbuy
     * https://www.afterbuy.de/afterbuy/ShopInterface.aspx
     * @var string
     */
    protected $_sFcAfterbuyShopInterfaceBaseUrl = "";

    /**
     * ABI Url of Afterbuy
     * http://api.afterbuy.de/afterbuy/ABInterface.aspx
     * @var string
     */
    protected $_sFcAfterbuyAbiUrl = "";

    /**
     * Partner ID of Afterbuy
     * @var string
     */
    protected $_sFcAfterbuyPartnerId = "";

    /**
     * Partner Password for Afterbuy
     * @var string
     */
    protected $_sFcAfterbuyPartnerPassword = "";

    /**
     * Username for Afterbuy
     * @var string
     */
    protected $_sFcAfterbuyUsername = "";

    /**
     * User password for Afterbuy
     * @var string
     */
    protected $_sFcAfterbuyUserPassword = "";


    /**
     * fcafterbuyapi constructor.
     *
     * The foreseen configuration that is needed has to be a filled array like this
     * $aConfig = array(
     *      'sFcAfterbuyShopInterfaceBaseUrl' => <AfterbuyShopInterfaceBaseUrl>,
     *      'sFcAfterbuyAbiUrl' => <AfterbuyAbiUrl>,
     *      'sFcAfterbuyPartnerId' => <AfterbuyPartnerId>,
     *      'sFcAfterbuyPartnerPassword' => <AfterbuyPartnerPassword>,
     *      'sFcAfterbuyUsername' => <AfterbuyUsername>,
     *      'sFcAfterbuyUserPassword' => <AfterbuyUserPassword>,
     *      'iFcLogLevel' => <LogLevel>,
     * );
     *
     * @param $aConfig
     * @throws Exception
     */
    function __construct($aConfig) {
        try {
            $this->_fcSetConfig($aConfig);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Central api logging method. Timestamp will be added automatically.
     * Logs only if loglevel matches
     *
     * @param string $sMessage
     * @param int $iLogLevel
     * @return void
     * @access protected
     */
    public function fcWriteLog($sMessage, $iLogLevel = 1) {
        // it is mandatory that a logfilepath has to be set
        if ($this->_sFcAfterbuyLogFilepath === null) return;

        $sTime = date("Y-m-d H:i:s");
        $sFullMessage = "[" . $sTime . "] " . $sMessage . "\n";
        if ($iLogLevel <= $this->_iFcLogLevel) {
            file_put_contents($this->_sFcAfterbuyLogFilepath, $sFullMessage, FILE_APPEND);
        }
    }

    /**
     * Sets the path for api logs
     *
     * @param $sPath
     * @return void
     */
    public function fcSetLogFilePath($sPath) {
        $this->_sFcAfterbuyLogFilepath = $sPath;
    }

    /**
     * Request Afterbuy API with given XML Request
     *
     * @param string $sXmlData
     * @return string API answer
     * @access protected
     */
    public function fcRequestAPI($sXmlData) {
        $this->fcWriteLog("DEBUG: Requesting Afterbuy API:\n".$sXmlData."\n",4);
        $ch = curl_init($this->_sFcAfterbuyAbiUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, "$sXmlData");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $sOutput = curl_exec($ch);
        curl_close($ch);
        $this->fcWriteLog("DEBUG: RESPONSE of Afterbuy API:\n".$sOutput."\n",4);
        return $sOutput;
    }

    /**
     * Updates or inserts article to afterbuy and returns API answer
     *
     * @param $oArt
     * @return string
     */
    public function updateArticleToAfterbuy($oArt) {
        $this->fcWriteLog("MESSAGE: Transfer article to afterbuy:".print_r($oArt,true));
        $sXmlData = $this->_fcGetUpdateArticleXml($oArt);
        $sOutput = $this->fcRequestAPI($sXmlData);

        return $sOutput;
    }

    /**
     * Requesting afterbuy api for sold products (orders)
     *
     * @param void
     * @return string
     */
    public function getSoldItemsFromAfterbuy() {
        $sXmlData = $this->_fcGetXmlHead('GetSoldItems', 0);
        $sXmlData .= "<MaxSoldItems>99</MaxSoldItems>";
        $sXmlData .= "<OrderDirection>1</OrderDirection>";
        $sXmlData .= $this->_fcGetXmlFoot();

        $sOutput = $this->fcRequestAPI($sXmlData);
        return $sOutput;
    }

    /**
     * Set configuration for afterbuy connection
     *
     * @param $aConfig
     * @return void
     */
    protected function _fcSetConfig($aConfig) {
        foreach ($aConfig as $sConfigName=>$sConfigValue) {
            $this->$sConfigName = $sConfigValue;
        }
    }

    /**
     * Request Afterbuy shop interface with REST URL
     *
     * @param string $sRequest
     * @return string API answer
     * @access public
     */
    public function fcRequestShopInterfaceAPI($sRequest) {
        // prepare parameters for post call
        $aRequest = explode("?", $sRequest);
        $sParamString = $aRequest[1];
        $aParamsWithValues = explode("&", $sParamString);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $aRequest[0]);
        curl_setopt($ch, CURLOPT_POST, count($aParamsWithValues));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $sParamString);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $sOutput = curl_exec($ch);
        curl_close($ch);

        return $sOutput;
    }

    /**
     * Returns xml for requesting aftberbuy abi
     *
     * @param $oArt
     * @param $sAbId
     * @return string
     */
    protected function _fcGetUpdateArticleXml($oArt) {
        $sXmlData = $this->_fcGetXmlHead('UpdateShopProducts');
        $sXmlData .= '<Products>
                        <Product>
                            <ProductIdent>';
        if (!$oArt->ProductID) {
            $sXmlData .= '<ProductInsert>1</ProductInsert>
                                <BaseProductType>0</BaseProductType>
                                <UserProductID><![CDATA[' . $oArt->UserProductID . ']]></UserProductID>
                                <Anr>' . $oArt->Anr . '</Anr>
                                <EAN>' . $oArt->EAN . '</EAN>';
        } else {
            $sXmlData .= '<ProductID>' . $oArt->ProductID . '</ProductID>';
        }
        $sXmlData .= '      </ProductIdent>
                            <UserProductID>' . $oArt->UserProductID . '</UserProductID>
                            <Anr>' . $oArt->Anr . '</Anr>
                            <EAN>' . $oArt->EAN . '</EAN>
                            <Name><![CDATA[' . $oArt->Name . ']]></Name>
                            <ShortDescription><![CDATA[' . $oArt->ShortDescription . ']]></ShortDescription>
                            <Description><![CDATA[' . $oArt->Description . ']]></Description>
                            <Quantity>' . $oArt->Quantity . '</Quantity>
                            <Stock>1</Stock>
                            <Discontinued>1</Discontinued>
                            <MergeStock>1</MergeStock>
                            <SellingPrice>' . $oArt->SellingPrice . '</SellingPrice>
                            <ImageSmallURL>'.$oArt->ImageSmallURL.'</ImageSmallURL>
                            <ImageLargeURL>'.$oArt->ImageLargeURL.'</ImageLargeURL>
                            <ProductBrand>'.$oArt->ProductBrand.'</ProductBrand>
                            <TaxRate>'.$oArt->TaxRate.'</TaxRate>
                            <ItemSize>'.$oArt->ItemSize.'</ItemSize>
                            <CanonicalUrl>'.$oArt->CanonicalUrl.'</CanonicalUrl>
                            <ManufacturerPartNumber>'.$oArt->ManufacturerPartNumber.'</ManufacturerPartNumber>
                            <Keywords>'.$oArt->Keywords.'</Keywords>
                            <BuyingPrice>'.$oArt->BuyingPrice.'</BuyingPrice>
                            <Weight>'.$oArt->Weight.'</Weight>
        ';
        if ($oArt->EAN != "") {
            $sXmlData .= '<ManufacturerStandardProductIDType><![CDATA[EAN]]></ManufacturerStandardProductIDType>
                            <ManufacturerStandardProductIDValue><![CDATA[' . $oArt->EAN . ']]></ManufacturerStandardProductIDValue>';
        }
        $sXmlData .= '<ProductPictures>';
        for($iIndex=1;$iIndex<=12;$iIndex++) {
            $sPictureAttribute = 'ProductPicture_Url_'.$iIndex;
            $sPictureUrl = $oArt->$sPictureAttribute;
            if (!$sPictureUrl) continue;
            $sXmlData .= '<ProductPicture>
                            <Nr>'.$iIndex.'</Nr>
                            <Url>' . $sPictureUrl . '</Url>
                            <AltText><![CDATA[' . $oArt->Name . ']]></AltText>        
                          </ProductPicture>';
        }
        $sXmlData .= '</ProductPictures>';
        $sXmlData .= '</Product></Products>';
        $sXmlData .= $this->_fcGetXmlFoot();

        return $sXmlData;
    }

    /**
     * Returns head part of xml request including auth information
     * 
     * @param $sCallName
     * @param int $iDetailLevel
     * @return string
     */
    protected function _fcGetXmlHead($sCallName, $iDetailLevel = 0) {
        $sXml = '<?xml version="1.0" encoding="utf-8"?>
                <Request>
                    <AfterbuyGlobal>
                        <PartnerID>' . $this->_sFcAfterbuyPartnerId . '</PartnerID>
                        <PartnerPassword><![CDATA[' . $this->_sFcAfterbuyPartnerPassword . ']]></PartnerPassword>
                        <UserID><![CDATA[' . $this->_sFcAfterbuyUsername . ']]></UserID>
                        <UserPassword><![CDATA[' . $this->_sFcAfterbuyUserPassword . ']]></UserPassword>
                        <CallName>' . $sCallName . '</CallName>
                        <DetailLevel>' . $iDetailLevel . '</DetailLevel>
                        <ErrorLanguage>DE</ErrorLanguage>
                    </AfterbuyGlobal>';
        return $sXml;
    }

    /**
     * Foot of xml request
     * 
     * @param void
     * @return string
     */
    protected function _fcGetXmlFoot() {
        $sXml = '</Request>';
        return $sXml;
    }

    /**
     * Test API in SW
     *
     * @param void
     * @return string
     */
    public function fcGetAfterbuyTime() {
        $sXmlData = $this->_fcGetXmlHead('GetAfterbuyTime');
        $sXmlData .= $this->_fcGetXmlFoot();
        $test = $this->fcRequestAPI($sXmlData);
        return $test;
    }
}

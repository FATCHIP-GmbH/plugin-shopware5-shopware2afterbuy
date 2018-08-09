<?php

namespace Shopware\CustomModels\FatchipShopware2Afterbuy;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * @ORM\Table(name="s_plugin_fatchip_shopware2Afterbuy_plugin_config")
 * @ORM\Entity(repositoryClass="PluginConfigRepository")
 */
class PluginConfig extends ModelEntity
{
    /**
     * Primary Key - autoincrement value
     *
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string $afterbuyShopInterfaceBaseUrl
     *
     * @ORM\Column(name="afterbuy_shop_interface_base_url", type="string", nullable=true)
     */
    private $afterbuyShopInterfaceBaseUrl;

    /**
     * @var string $afterbuyAbiUrl
     *
     * @ORM\Column(name="afterbuy_abi_url", type="string", nullable=true)
     */
    private $afterbuyAbiUrl;

    /**
     * @var string $afterbuyPartnerId
     *
     * @ORM\Column(name="afterbuy_partner_id", type="string", nullable=true)
     */
    private $afterbuyPartnerId;

    /**
     * @var string $afterbuyPartnerPassword
     *
     * @ORM\Column(name="afterbuy_partner_password", type="string", nullable=true)
     */
    private $afterbuyPartnerPassword;

    /**
     * @var string $afterbuyUsername
     *
     * @ORM\Column(name="afterbuy_username", type="string", nullable=true)
     */
    private $afterbuyUsername;

    /**
     * @var string $afterbuyUserpassword
     *
     * @ORM\Column(name="afterbuy_userpassword", type="string", nullable=true)
     */
    private $afterbuyUserpassword;

    /**
     * @var string $ordernumberMapping
     *
     * @ORM\Column(name="ordernumber_mapping", type="string", nullable=true)
     */
    private $ordernumberMapping;

    /**
     * @var string $logLevel
     *
     * @ORM\Column(name="log_level", type="string", nullable=true)
     */
    private $logLevel;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getAfterbuyShopInterfaceBaseUrl()
    {
        return $this->afterbuyShopInterfaceBaseUrl;
    }

    /**
     * @param string $afterbuyShopInterfaceBaseUrl
     */
    public function setAfterbuyShopInterfaceBaseUrl(string $afterbuyShopInterfaceBaseUrl)
    {
        $this->afterbuyShopInterfaceBaseUrl = $afterbuyShopInterfaceBaseUrl;
    }

    /**
     * @return string
     */
    public function getAfterbuyAbiUrl()
    {
        return $this->afterbuyAbiUrl;
    }

    /**
     * @param string $afterbuyAbiUrl
     */
    public function setAfterbuyAbiUrl(string $afterbuyAbiUrl)
    {
        $this->afterbuyAbiUrl = $afterbuyAbiUrl;
    }

    /**
     * @return string
     */
    public function getAfterbuyPartnerId()
    {
        return $this->afterbuyPartnerId;
    }

    /**
     * @param string $afterbuyPartnerId
     */
    public function setAfterbuyPartnerId(string $afterbuyPartnerId)
    {
        $this->afterbuyPartnerId = $afterbuyPartnerId;
    }

    /**
     * @return string
     */
    public function getAfterbuyPartnerPassword()
    {
        return $this->afterbuyPartnerPassword;
    }

    /**
     * @param string $afterbuyPartnerPassword
     */
    public function setAfterbuyPartnerPassword(string $afterbuyPartnerPassword)
    {
        $this->afterbuyPartnerPassword = $afterbuyPartnerPassword;
    }

    /**
     * @return string
     */
    public function getAfterbuyUsername()
    {
        return $this->afterbuyUsername;
    }

    /**
     * @param string $afterbuyUsername
     */
    public function setAfterbuyUsername(string $afterbuyUsername)
    {
        $this->afterbuyUsername = $afterbuyUsername;
    }

    /**
     * @return string
     */
    public function getAfterbuyUserpassword()
    {
        return $this->afterbuyUserpassword;
    }

    /**
     * @param string $afterbuyUserpassword
     */
    public function setAfterbuyUserpassword(string $afterbuyUserpassword)
    {
        $this->afterbuyUserpassword = $afterbuyUserpassword;
    }

    /**
     * @return string
     */
    public function getOrdernumberMapping() {
        return $this->ordernumberMapping;
    }

    /**
     * @param string $ordernumberMapping
     */
    public function setOrdernumberMapping($ordernumberMapping) {
        $this->ordernumberMapping = $ordernumberMapping;
    }

    /**
     * @return string
     */
    public function getLogLevel()
    {
        return $this->logLevel;
    }

    /**
     * @param string $logLevel
     */
    public function setLogLevel(string $logLevel)
    {
        $this->logLevel = $logLevel;
    }


    /**
     * @return string
     */
    public function getAfterbuyLogfilePath()
    {
        $logPath  = Shopware()->Container()->getParameter('kernel.logs_dir');

        return $logPath . '/fcAfterbuy.log';
    }

    /**
     * @return array
     */
    public function toCompatArray()
    {
        return [
            'afterbuyShopInterfaceBaseUrl' => $this->getAfterbuyShopInterfaceBaseUrl(),
            'afterbuyAbiUrl' => $this->getAfterbuyAbiUrl(),
            'afterbuyPartnerId' => $this->getAfterbuyPartnerId(),
            'afterbuyPartnerPassword' => $this->getAfterbuyPartnerPassword(),
            'afterbuyUsername' => $this->getAfterbuyUsername(),
            'afterbuyUserPassword' => $this->getAfterbuyUserpassword(),
            'ordernumberMapping' => $this->getOrdernumberMapping(),
            'logLevel' => $this->getLogLevel(),
            'afterbuyLogFilepath' => $this->getAfterbuyLogfilePath(),
        ];
    }

}

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
     * @var string $clientUser
     *
     * @ORM\Column(name="client_user", type="string", nullable=true)
     */
    private $clientUser;

     /**
     * @var boolean $liveMode
     *
     * @ORM\Column(name="live_mode", type="boolean", nullable=false, options={"default":false})
     */
    private $liveMode;

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
    public function getClientUser()
    {
        return $this->clientUser;
    }

    /**
     * @param $clientUser string
     */
    public function setClientUser($clientUser)
    {
        $this->clientUser = $clientUser;
    }

     /**
     * @return bool
     */
    public function isLiveMode()
    {
        return $this->liveMode;
    }

    /**
     * @param bool $liveMode
     */
    public function setLiveMode($liveMode)
    {
        $this->liveMode = $liveMode;
    }
}

<?php

namespace viaebShopwareAfterbuy\Models;

use Doctrine\ORM\Mapping as ORM;
use \Shopware\Components\Model\ModelEntity;

/**
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="afterbuy_status")
 */
class Status extends ModelEntity {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    private $lastOrderImport;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    private $lastProductImport;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    private $lastProductExport;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    private $lastStatusExport;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return \DateTime
     */
    public function getLastOrderImport()
    {
        return $this->lastOrderImport;
    }

    /**
     * @param \DateTime $lastOrderImport
     */
    public function setLastOrderImport(\DateTime $lastOrderImport)
    {
        $this->lastOrderImport = $lastOrderImport;
    }

    /**
     * @return \DateTime
     */
    public function getLastProductImport()
    {
        return $this->lastProductImport;
    }

    /**
     * @param \DateTime $lastProductImport
     */
    public function setLastProductImport(\DateTime $lastProductImport)
    {
        $this->lastProductImport = $lastProductImport;
    }

    /**
     * @return \DateTime
     */
    public function getLastStatusExport()
    {
        return $this->lastStatusExport;
    }

    /**
     * @param \DateTime $lastStatusExport
     */
    public function setLastStatusExport(\DateTime $lastStatusExport)
    {
        $this->lastStatusExport = $lastStatusExport;
    }

    /**
     * @return \DateTime
     */
    public function getLastProductExport()
    {
        return $this->lastProductExport;
    }

    /**
     * @param \DateTime $lastProductExport
     */
    public function setLastProductExport(\DateTime $lastProductExport)
    {
        $this->lastProductExport = $lastProductExport;
    }


}
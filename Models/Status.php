<?php

namespace abaccAfterbuy\Models;

use Doctrine\ORM\Mapping as ORM;
use \Shopware\Components\Model\ModelEntity;

/**
 * @ORM\Entity
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
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return \DateTime
     */
    public function getLastOrderImport(): \DateTime
    {
        return $this->lastOrderImport;
    }

    /**
     * @param \DateTime $lastOrderImport
     */
    public function setLastOrderImport(\DateTime $lastOrderImport): void
    {
        $this->lastOrderImport = $lastOrderImport;
    }

    /**
     * @return \DateTime
     */
    public function getLastProductImport(): \DateTime
    {
        return $this->lastProductImport;
    }

    /**
     * @param \DateTime $lastProductImport
     */
    public function setLastProductImport(\DateTime $lastProductImport): void
    {
        $this->lastProductImport = $lastProductImport;
    }

    /**
     * @return \DateTime
     */
    public function getLastStatusExport(): \DateTime
    {
        return $this->lastStatusExport;
    }

    /**
     * @param \DateTime $lastStatusExport
     */
    public function setLastStatusExport(\DateTime $lastStatusExport): void
    {
        $this->lastStatusExport = $lastStatusExport;
    }

    /**
     * @return \DateTime
     */
    public function getLastProductExport(): \DateTime
    {
        return $this->lastProductExport;
    }

    /**
     * @param \DateTime $lastProductExport
     */
    public function setLastProductExport(\DateTime $lastProductExport): void
    {
        $this->lastProductExport = $lastProductExport;
    }


}
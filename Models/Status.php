<?php

namespace FatchipAfterbuy\Models;

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
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    private $lastOrderImport;

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


}
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
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    private $lastOrderImport;

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
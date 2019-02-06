<?php

namespace FatchipAfterbuy\Services\WriteData;

use Doctrine\ORM\OptimisticLockException;
use FatchipAfterbuy\Components\Helper;
use FatchipAfterbuy\Services\AbstractDataService;

class AbstractWriteDataService extends AbstractDataService {

    /**
     * @var ModelEntity
     */
    protected $targetRepository;


    /**
     * @param string $repo
     */
    public function setRepo(string $repo) {
        $this->targetRepository = $repo;
    }


    /**
     * @param string $field
     * @throws OptimisticLockException
     */
    public function storeSubmissionDate(string $field) {
        $status = $this->entityManager->getRepository('\FatchipAfterbuy\Models\Status')->find(1);

        $setter = Helper::getSetterByField($field);

        $status->$setter(new \DateTime());

        $this->entityManager->persist($status);
        $this->entityManager->flush();
    }
}
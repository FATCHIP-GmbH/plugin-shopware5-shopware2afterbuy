<?php

namespace abaccAfterbuy\Services\WriteData;

use abaccAfterbuy\Components\Helper;
use abaccAfterbuy\Services\AbstractDataService;
use abaccAfterbuy\Models\Status;

class AbstractWriteDataService extends AbstractDataService {

    /**
     * @var string
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
     */
    public function storeSubmissionDate(string $field) {
        $status = $this->entityManager->getRepository(Status::class)->find(1);

        $setter = Helper::getSetterByField($field);

        try {
            $status->$setter(new \DateTime());
            $this->entityManager->persist($status);
            $this->entityManager->flush();
        }
        catch(\Exception $e) {
            $this->logger->error('Error updating submission date', array($field));
        }
    }
}
<?php

namespace FatchipAfterbuy\Services\WriteData;

use FatchipAfterbuy\Components\Helper;
use FatchipAfterbuy\Services\AbstractDataService;
use Psr\Log\LoggerInterface;
use Shopware\Components\DependencyInjection\Bridge\ModelAnnotation;
use Shopware\Components\Model\ModelManager;
use FatchipAfterbuy\Models\Status;

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

    public function storeSubmissionDate(string $field) {
        $status = $this->entityManager->getRepository(Status::class)->find(1);

        $setter = Helper::getSetterByField($field);

        $status->$setter(new \DateTime());

        $this->entityManager->persist($status);
        $this->entityManager->flush();
    }
}
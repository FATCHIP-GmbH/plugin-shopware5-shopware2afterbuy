<?php

namespace viaebShopwareAfterbuy\Services\WriteData;

use viaebShopwareAfterbuy\Components\Helper;
use viaebShopwareAfterbuy\Services\AbstractDataService;
use viaebShopwareAfterbuy\Models\Status;

class AbstractWriteDataService extends AbstractDataService {

    /**
     * @var string
     */
    protected $targetRepository;

    /**
     * @param string $repo
     */
    public function setRepo(string $repo): void
    {
        $this->targetRepository = $repo;
    }

    /**
     * @param string $field
     */
    public function storeSubmissionDate(string $field): void
    {
        $status = $this->entityManager->getRepository(Status::class)->find(1);

        $setter = Helper::getSetterByField($field);

        if(empty($status)) {
            return;
        }

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
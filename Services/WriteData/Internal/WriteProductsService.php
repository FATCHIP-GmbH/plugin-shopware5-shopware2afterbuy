<?php

namespace FatchipAfterbuy\Services\WriteData\Internal;

use Doctrine\ORM\OptimisticLockException;
use FatchipAfterbuy\Models\Status;
use FatchipAfterbuy\Services\Helper\ShopwareArticleHelper;
use FatchipAfterbuy\Services\WriteData\AbstractWriteDataService;
use FatchipAfterbuy\Services\WriteData\WriteDataInterface;
use FatchipAfterbuy\ValueObjects\Article as ValueArticle;
use Shopware\Models\Customer\Group as CustomerGroup;


class WriteProductsService extends AbstractWriteDataService implements WriteDataInterface
{

    /** @var ShopwareArticleHelper $helper */
    public $helper;

    /**
     * @param array $data
     *
     * @return array
     */
    public function put(array $data): array
    {
        $this->transform($data);

        return $this->send($data);
    }

    /**
     * transforms valueObject into final structure for storage
     * could may be moved into separate helper
     *
     * @param ValueArticle[] $valueArticles
     */
    public function transform(array $valueArticles)
    {
        $this->logger->debug('Importing articles', $valueArticles);

        /** @var CustomerGroup $customerGroup */
        $customerGroup = $this->entityManager->getRepository(CustomerGroup::class)->findOneBy(
            array('id' => $this->config['customerGroup'])
        );
        $netInput = $customerGroup->getTaxInput();

        if ( ! $customerGroup) {
            $this->logger->error('Target customer group not set', array('Import', 'Articles'));

            return;
        }

        $this->helper->importArticle($valueArticles, $netInput, $customerGroup);

        // Category Association
        $this->helper->associateCategories($valueArticles);

        // Image Association
        $this->helper->associateImages($valueArticles, $this);
    }


    /**
     * @param array $targetData
     *
     * @return array
     */
    public function send($targetData): array
    {
        try {
            $this->entityManager->flush();
        } catch (OptimisticLockException $e) {
            $this->logger->error('Error storing products', $targetData);
        }

        $this->storeSubmissionDate('lastProductImport');
        $this->helper->setArticlesWithoutAnyActiveVariantToInactive();

        return $targetData;
    }

    public function getArticleImportDateFilter($force = false)
    {
        if ($force) {
            return array();
        }

        /** @var $lastDate Status */
        $lastDate = $this->entityManager->getRepository(Status::class)->find(1);

        if ( ! $lastDate) {
            return array();
        }

        if ( ! $lastDate->getLastProductImport()) {
            return array();
        }

        $filterDate = date_format($lastDate->getLastProductImport(), 'd.m.Y H:i:s');

        $filter = array(
            'Filter' => array(
                'FilterName'   => 'DateFilter',
                'FilterValues' => array(
                    'DateFrom'    => $filterDate,
                    'FilterValue' => 'ModDate',
                ),
            ),
        );

        return $filter;
    }
}

<?php

namespace viaebShopwareAfterbuy\Services\WriteData\Internal;

use Doctrine\ORM\OptimisticLockException;

use Doctrine\ORM\ORMException;
use viaebShopwareAfterbuy\Models\Status;
use viaebShopwareAfterbuy\Services\Helper\ShopwareArticleHelper;
use viaebShopwareAfterbuy\Services\WriteData\AbstractWriteDataService;
use viaebShopwareAfterbuy\Services\WriteData\WriteDataInterface;
use viaebShopwareAfterbuy\ValueObjects\Article as ValueArticle;
use Shopware\Models\Customer\Group as CustomerGroup;


class WriteProductsService extends AbstractWriteDataService implements WriteDataInterface
{
    /**
     * @param array $data
     *
     * @return array
     * @throws ORMException
     */
    public function put(array $data)
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

        if ( ! $customerGroup) {
            $this->logger->error('Target customer group not set', array('Import', 'Articles'));

            exit('Target customer group not set');
        }

        $netInput = $customerGroup->getTaxInput();

        $this->helper->importArticle($valueArticles, $netInput, $customerGroup);

        // Category Association
        $this->helper->associateCategories($valueArticles);

        // Image Association
        $this->helper->associateImages($valueArticles);
    }


    /**
     * @param array $targetData
     *
     * @return array
     */
    public function send($targetData)
    {
        try {
            $this->entityManager->flush();
        } catch (OptimisticLockException $e) {
            $this->logger->error('Error storing products', $targetData);
            exit('Error storing products');
        } catch (ORMException $e) {
        }

        if(!empty($targetData)) {
            $this->storeSubmissionDate('lastProductImport');
            $this->helper->setArticlesWithoutAnyActiveVariantToInactive();
        }

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

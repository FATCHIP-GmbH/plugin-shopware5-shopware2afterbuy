<?php

namespace viaebShopwareAfterbuy\Services\WriteData\Internal;

use Doctrine\ORM\OptimisticLockException;

use Doctrine\ORM\ORMException;
use viaebShopwareAfterbuy\Services\Helper\ShopwareArticleHelper;
use viaebShopwareAfterbuy\Services\WriteData\AbstractWriteDataService;
use viaebShopwareAfterbuy\Services\WriteData\WriteDataInterface;
use viaebShopwareAfterbuy\ValueObjects\Stock;

/**
 * @property ShopwareArticleHelper $helper
 */
class WriteStockService extends AbstractWriteDataService implements WriteDataInterface
{
    /**
     * @param array $data
     *
     * @return array
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
     * @param Stock[] $valueArticles
     * @return array|Stock[]
     */
    public function transform(array $valueArticles)
    {
        $this->logger->debug('Importing stock', $valueArticles);

        foreach ($valueArticles as $article) {

            $detail = $this->helper->getArticleByExternalIdentifier($article->getIdentifyer());

            if($detail === null || !is_int($article->getStock())) {
                continue;
            }

            $detail->setInStock($article->getStock());

            try {
                $this->entityManager->persist($detail);
            } catch (ORMException $e) {
                $this->logger->error('Error storing stock', $article);
            }

        }

        return $valueArticles;
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
        } catch (OptimisticLockException | ORMException $e) {
            $this->logger->error('Error storing stock', $targetData);
            exit('Error storing stock');
        }

        return $targetData;
    }
}

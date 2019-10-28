<?php
/** @noinspection SpellCheckingInspection */

namespace viaebShopwareAfterbuy\Services\Helper;

use DateTime;
use Doctrine\ORM\Query;
use Exception;
use Doctrine\ORM\Mapping\ClassMetadata;
use viaebShopwareAfterbuy\Models\Status;

class ShopwareResetHelper extends AbstractHelper
{
    protected $shopwareAttributesArray;

    public function initHelper($entities)
    {
        $this->shopwareAttributesArray = $entities;
    }

    public function resetShopConnection()
    {
        $result = $this->resetAttributes();

        // reset table afterbuy_status
        $this->resetStatus();

        return $result;
    }

    /**
     * @return array
     */
    private function resetAttributes()
    {
        $result = [
            'msg' => 'success',
            'data' => [],
        ];

        foreach ($this->shopwareAttributesArray as $shopwareAttributes) {
            if ($this->resetShopConnectionEntity($shopwareAttributes) !== 'success') {
                $result['msg'] = 'failure';
                $result['data'][] = $shopwareAttributes;
            }
        }
        return $result;
    }

    private function resetShopConnectionEntity($shopwareAttributes)
    {
        $prefix = 'afterbuy';

        /** @var ClassMetadata $metadata */
        $metadata = $this->entityManager->getClassMetadata($shopwareAttributes);

        $builder = $this->entityManager->createQueryBuilder();
        $builder->update($shopwareAttributes, 'a');

        foreach ($metadata->fieldMappings as $name => $column) {
            // skip column names not starting with 'afterbuy'
            if (substr($name, 0, strlen($prefix)) !== $prefix) {
                continue;
            }

            $builder->set('a.' . $name, ':null');
        }

        /** @var Query $builder */
        try {
            $builder
                ->setParameter('null', null)
                ->getQuery()
                ->execute();

        } catch (Exception $e) {
            return 'failure';
        }

        return 'success';
    }

    private function resetStatus()
    {
        /** @var ClassMetadata $metadata */
        $metadata = $this->entityManager->getClassMetadata(Status::class);

        $builder = $this->entityManager->createQueryBuilder();
        $builder->update(Status::class, 'a');

        foreach ($metadata->fieldMappings as $name => $column) {
            // skip id column
            if ($column['type'] !== 'datetime') {
                continue;
            }

            $builder->set('a.' . $name, ':null');
        }

        try {
            $builder
                ->setParameter('null', new DateTime('01-01-1970'))
                ->getQuery()
                ->execute();
        } catch (Exception $e) {
        }
    }
}

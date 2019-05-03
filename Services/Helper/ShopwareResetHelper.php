<?php

namespace viaebShopwareAfterbuy\Services\Helper;

use DateTime;
use Doctrine\ORM\Query;
use Exception;
use Doctrine\ORM\Mapping\ClassMetadata;
use viaebShopwareAfterbuy\Models\Status;

class ShopwareResetHelper extends AbstractHelper
{
    protected $entities;

    public function initHelper($entities)
    {
        $this->entities = $entities;
    }

    public function resetShopConnection()
    {
        $result = [
            'msg' => 'success',
            'data' => [],
        ];

        //TODO: separate into methods (1. reset attributes, 2. reset status)
        // reset all afterbuy attributes
        foreach ($this->entities as $entity) {
            if ($this->resetShopConnectionEntity($entity) !== 'success') {
                $result['msg'] = 'failure';
                $result['data'][] = $entity;
            }
        }

        // reset table afterbuy_status

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

        return $result;
    }

    private function resetShopConnectionEntity($entity)
    {
        $prefix = 'afterbuy';

        /** @var ClassMetadata $metadata */
        $metadata = $this->entityManager->getClassMetadata($entity);

        $builder = $this->entityManager->createQueryBuilder();
        $builder->update($entity, 'a');

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
}
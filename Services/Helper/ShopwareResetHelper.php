<?php

namespace viaebShopwareAfterbuy\Services\Helper;

use Doctrine\ORM\Mapping\ClassMetadata;

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

        foreach ($this->entities as $entity) {
            if ($this->resetShopConnectionEntity($entity) !== 'success') {
                $result['msg'] = 'failure';
                $result['data'][] = $entity;
            }
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

        $builder
            ->setParameter('null', null)
            ->getQuery()
            ->execute();

        return 'success';
    }
}
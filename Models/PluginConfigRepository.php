<?php

namespace Shopware\CustomModels\FatchipShopware2Afterbuy;

use Shopware\Components\Model\ModelRepository;

/**
 * Repository class for PluginConfig
 *
 * @package Shopware\CustomModels\FatchipShopware2Afterbuy
 */
class PluginConfigRepository extends ModelRepository
{

    /**
     * Returns an instance of the \Doctrine\ORM\Query object which selects a list of PluginConfig
     *
     * @param $filter
     * @param $orderBy
     * @param $offset
     * @param $limit
     * @return \Doctrine\ORM\Query
     */
    public function getListQuery($filter = null, $orderBy = null, $offset = null, $limit = null)
    {
        $builder = $this->getListQueryBuilder($filter, $orderBy);
        $builder->setFirstResult($offset)
            ->setMaxResults($limit);
        return $builder->getQuery();
    }

    /**
     * Helper function to create the query builder for the "getListQuery" function.
     * This function can be hooked to modify the query builder of the query object.
     *
     * @param $filter
     * @param $orderBy
     * @return \Shopware\Components\Model\QueryBuilder
     */
    public function getListQueryBuilder($filter = null, $orderBy = null)
    {
        /** @var \Shopware\Components\Model\QueryBuilder $builder */
        $builder = $this->getEntityManager()->createQueryBuilder();

        $builder->select(['plugin_config'])
            ->from($this->getEntityName(), 'plugin_config');

        $this->addFilter($builder, $filter);
        $this->addOrderBy($builder, $orderBy);

        return $builder;
    }
}

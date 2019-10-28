<?php

namespace viaebShopwareAfterbuy\Services\Helper;

class ShopwareConfigHelper extends AbstractHelper
{
    public static $AB_UNI_PAYMENT = 'ab_uni';

    public function getConfigValues($pluginName) {
        $query = $this->dbal->createQueryBuilder();

        $query->select([
            'element.name',
            'element.value as def',
            'elementValues.value as value',
        ]);

        $query->from('s_core_config_elements', 'element')
            ->leftJoin('element', 's_core_config_values', 'elementValues', 'elementValues.element_id = element.id AND elementValues.shop_id = :shopId')
            ->setParameter(':shopId', 1);

        $query->innerJoin('element', 's_core_config_forms', 'elementForm', 'elementForm.id = element.form_id')
            ->andWhere('elementForm.name = :namespace')
            ->setParameter(':namespace', $pluginName);

        $values = $query->execute()->fetchAll();

        $result = [];

        foreach($values as $value) {
            $result[$value['name']] = empty($value['value']) ? unserialize($value['def']) : unserialize($value['value']);
        }

        return $result;
    }
}
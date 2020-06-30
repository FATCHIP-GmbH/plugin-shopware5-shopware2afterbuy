<?php

namespace viaebShopwareAfterbuy\Services\Helper;

class ShopwareConfigHelper extends AbstractHelper
{
    public static $AB_UNI_PAYMENT = 'ab_uni';
    /** when problem with asynchronous config tool happens again, adjust this value */
    public static $HIGHEST_KNOWN_VERSION_THAT_STORES_VALUES_AS_INTEGER = '5.5.7';

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

        $specialValues = [
            'mainSystem',
            'ordernumberMapping',
            'ExportAllArticles',
        ];

        foreach($values as $value) {
            $rawValue = empty($value['value']) ? unserialize($value['def']) : unserialize($value['value']);

            $version = self::$HIGHEST_KNOWN_VERSION_THAT_STORES_VALUES_AS_INTEGER;
            if (self::getShopwareVersion() <= $version && in_array($value['name'], $specialValues)) {
                $rawValue = strval($rawValue);
            }
            $result[$value['name']] = $rawValue;
        }

        return $result;
    }
}
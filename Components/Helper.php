<?php

namespace viaebShopwareAfterbuy\Components;

class Helper {
    /**
     * returns setter name as string for given property
     *
     * @param string $field
     * @return string
     */
    public static function getSetterByField(string $field) {
        return 'set' . strtoupper($field[0]) . substr($field, 1);
    }

    /**
     * @param string $value
     * @return float
     */
    public static function convertDeString2Float(string $value) {
        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);

        return (float)$value;
    }

    /**
     * @param $value
     * @return mixed|string
     */
    public static function convertNumberToABString($value) {
        $value = number_format((float)$value, 2);
        $value = str_replace(',', '', $value);
        $value = str_replace('.', ',', $value);

        return $value;
    }

    /**
     * @param float $price
     * @param float $tax
     * @param bool $isNet
     * @param bool $toNet
     * @return float|int
     */
    public static function convertPrice(float $price, float $tax, $isNet = true, $toNet = false) {
        if($isNet === $toNet) {
            return $price;
        }

        if(!$tax) {
            return $price;
        }

        if ($isNet) {
            return $price * (1 + ($tax / 100));
        }

        return $price / (1 + ($tax / 100));
    }
}
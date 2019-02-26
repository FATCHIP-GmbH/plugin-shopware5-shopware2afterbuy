<?php

namespace viaebShopware2Afterbuy\Components;

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

    public static function convertDeString2Float(string $value) {
        $value = str_replace(".", "", $value);
        $value = str_replace(",", ".", $value);

        return floatval($value);
    }

    public static function convertNumberToABString($value) {
        $value = number_format($value, 2);
        $value = str_replace(",", "", $value);
        $value = str_replace(".", ",", $value);

        return $value;
    }

    public static function convertPrice(float $price, float $tax, $isNet = true, $toNet = false) {
        if($isNet == $toNet) {
            return $price;
        }

        if(!$tax) {
            return $price;
        }

        if ($isNet) {
            return $price * (1 + ($tax / 100));
        }
        else {
            return $price / (1 + ($tax / 100));
        }
    }
}
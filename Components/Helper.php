<?php

namespace FatchipAfterbuy\Components;

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
}
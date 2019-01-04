<?php

namespace FatchipAfterbuy\Components;

class Helper {

    public static function getSetterByField(string $field) {
        return 'set' . strtoupper($field[0]) . substr($field, 1);
    }
}
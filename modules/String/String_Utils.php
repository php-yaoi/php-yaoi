<?php

class String_Utils {
    static function underscoresToCamelCase($s) {
        return implode('', array_map('ucfirst', explode('_', $s)));
    }

    static function camelCaseToUnderscores($s) {
        return strtolower(ltrim(preg_replace('/([A-Z])/', '_$1', $s), '_'));
    }
} 
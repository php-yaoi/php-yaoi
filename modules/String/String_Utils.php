<?php

class String_Utils {
    static function toCamelCase($string, $delimiter = '_') {
        return implode('', array_map('ucfirst', explode($delimiter, $string)));
    }

    static function fromCamelCase($string, $delimiter = '_') {
        return strtolower(ltrim(preg_replace('/([A-Z])/', $delimiter . '$1', $string), $delimiter));
    }
} 
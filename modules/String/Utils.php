<?php

namespace Yaoi\String;
class Utils
{
    static function toCamelCase($string, $delimiter = '_')
    {
        return implode('', array_map('ucfirst', explode($delimiter, $string)));
    }

    static function fromCamelCase($string, $delimiter = '_')
    {
        return strtolower(ltrim(preg_replace('/([A-Z])/', $delimiter . '$1', $string), $delimiter));
    }

    static function starts($haystack, $needle)
    {
        return substr($haystack, 0, strlen($needle)) === (string)$needle;
    }

    static function ends($haystack, $needle)
    {
        return substr($haystack, -strlen($needle)) === (string)$needle;
    }
} 
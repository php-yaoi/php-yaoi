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
        return strtolower(ltrim(preg_replace('/([A-Z]+)/', $delimiter . '$1', $string), $delimiter));
    }

    static function starts($haystack, $needle)
    {
        return substr($haystack, 0, strlen($needle)) === (string)$needle;
    }

    static function ends($haystack, $needle)
    {
        return substr($haystack, -strlen($needle)) === (string)$needle;
    }

    public static $strPosLastFound;
    public static function strPos($haystack, $needles, $offset = 0, $reverse = false, $ignoreCase = false)
    {
        self::$strPosLastFound = null;
        $result = false;
        if (!is_array($needles)) {
            $needles = array($needles);
        }

        $strpos = 'strpos';

        if ($reverse) {
            $strpos = $ignoreCase ? 'strripos' : 'strrpos';
        }
        elseif ($ignoreCase) {
            $strpos = 'stripos';
        }

        foreach ($needles as $needle) {
            if (false !== $position = $strpos($haystack, (string)$needle, $offset)) {
                if ($result === false) {
                    $result = $position;
                    self::$strPosLastFound = $needle;
                } else {
                    if ($reverse) {
                        if ($position > $result) {
                            self::$strPosLastFound = $needle;
                            $result = $position;
                        }
                    }
                    else {
                        if ($position < $result) {
                            self::$strPosLastFound = $needle;
                            $result = $position;
                        }
                    }
                }
            }

        }

        return $result;
    }

} 
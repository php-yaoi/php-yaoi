<?php

namespace Yaoi\String;

use Yaoi\BaseClass;

class StringValue extends BaseClass
{
    public $value = '';
    public function __construct($string) {
        $this->value = (string)$string;
    }

    public function starts($substring, $ignoreCase = false) {
        $strLen = strlen($substring);
        if ($ignoreCase) {
            return strtolower(substr($this->value, 0, $strLen)) === strtolower($substring);
        }
        else {
            return substr($this->value, 0, $strLen) === $substring;
        }
    }

    /**
     * Returns substring after provided start
     *
     * @param $substring
     * @param $ignoreCase
     * @return bool|string
     */
    public function afterStarts($substring, $ignoreCase = false) {
        $strLen = strlen($substring);
        if ($ignoreCase) {
            $starts = strtolower(substr($this->value, 0, $strLen)) === strtolower($substring);
        }
        else {
            $starts = substr($this->value, 0, $strLen) === $substring;
        }
        return $starts ? substr($this->value, $strLen) : false;
    }

    public function ends($substring, $ignoreCase = false) {
        $strLen = strlen($substring);
        if ($ignoreCase) {
            return strtolower(substr($this->value, -$strLen)) === strtolower($substring);
        }
        else {
            return substr($this->value, -$strLen) === $substring;
        }
    }

    public function __toString() {
        return $this->value;
    }

}
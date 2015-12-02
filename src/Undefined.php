<?php

namespace Yaoi;

class Undefined
{
    public static function get()
    {
        static $value;
        if (null === $value) {
            $value = new self();
        }
        return $value;
    }
}

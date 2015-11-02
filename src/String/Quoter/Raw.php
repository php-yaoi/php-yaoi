<?php

namespace Yaoi\String\Quoter;

use Yaoi\BaseClass;
use Yaoi\String\Quoter;

class Raw extends BaseClass implements Quoter
{
    public function quote($value)
    {
        if (is_array($value)) {
            $result = '';
            foreach ($value as $item) {
                $result .= $this->quote($item) . ', ';
            }
            return substr($result, 0, -2);
        }
        return (string)$value;
    }

    private static $instance;
    public static function create() {
        if (null === self::$instance) {
            self::$instance = new static;
        }
        return self::$instance;
    }

}
<?php


namespace Yaoi\Sql;
use Yaoi\BaseClass;

class Symbol extends BaseClass
{
    public $names = array();
    public $name;

    public function __construct($name, $name2 = null, $name3 = null)
    {
        foreach (func_get_args() as $nameItem) {
            if ($nameItem) {
                $this->names [] = $nameItem;
            }
        }
        $this->name = $this->names[0];
    }
} 
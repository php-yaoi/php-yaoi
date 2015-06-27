<?php


namespace Yaoi\Sql;
class Symbol
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
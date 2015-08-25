<?php


namespace Yaoi\Sql;
use Yaoi\BaseClass;
use Yaoi\Database\Definition\Column;

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


    /**
     * @param array|\stdClass $columns
     * @return array|Symbol[]
     */
    public static function prepareColumns($columns) {
        if ($columns instanceof \stdClass) {
            $columns = (array)$columns;
        }
        $result = array();
        foreach ($columns as $column) {
            if ($column instanceof Column) {
                $result []= new Symbol($column->schemaName);
            }
        }
        return $result;
    }
} 
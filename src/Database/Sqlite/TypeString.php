<?php
/**
 * Created by PhpStorm.
 * User: vpoturaev
 * Date: 8/2/15
 * Time: 17:34
 */

namespace Yaoi\Database\Sqlite;


use Yaoi\Database\Definition\Column;

class TypeString extends \Yaoi\Database\Mysql\TypeString
{

    protected $overrideDefault = false;
    protected function getFloatTypeString(Column $column)
    {
        return 'float';
    }


    protected function getIntTypeString(Column $column)
    {
        return 'INTEGER';
    }

    protected function getBaseType(Column $column) {
        if ($column->flags & Column::AUTO_TYPE) {
            return '';
        }

        return parent::getBaseType($column);
    }


    public function getByColumn(Column $column)
    {
        if ($column->flags & Column::AUTO_ID) {
            return 'INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL';
        }

        return parent::getByColumn($column);
    }
}
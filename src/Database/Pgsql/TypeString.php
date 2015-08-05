<?php

namespace Yaoi\Database\Pgsql;


use Yaoi\Database\Definition\Column;

class TypeString extends \Yaoi\Database\Mysql\TypeString
{
    public function getByColumn(Column $column)
    {
        if ($column->flags & Column::AUTO_ID) {
            return 'SERIAL';
        }

        return parent::getByColumn($column);
    }
}
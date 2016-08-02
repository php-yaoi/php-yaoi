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

        $flags = $column->flags;

        $typeString = $this->getBaseType($column);

        if ($flags & Column::UNSIGNED) {
            $typeString .= ' unsigned';
        }

        if ($flags & Column::NOT_NULL) {
            $typeString .= ' NOT NULL';
        }

        $default = $column->getDefault();
        if ((false !== $default) && null !== $default) {
            $typeString .= $this->database->expr(" DEFAULT ?", $default);
        }

        return $typeString;
    }

    protected function getIntTypeString(Column $column)
    {
        $intType = 'int';
        return $intType;
    }

    protected function getFloatTypeString(Column $column)
    {
        return 'float';
    }

}
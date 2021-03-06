<?php

namespace Yaoi\Database\Mysql;


use Yaoi\Database\Contract;
use Yaoi\Database\Definition\Column;
use Yaoi\Database\Exception;

class TypeString
{

    protected $database;

    public function __construct(Contract $database)
    {
        $this->database = $database;
    }


    protected function getIntTypeString(Column $column)
    {
        $intType = 'int';

        $flags = $column->flags;
        switch (true) {
            case $flags & Column::SIZE_1B:
                $intType = 'tinyint';
                break;

            case $flags & Column::SIZE_2B:
                $intType = 'smallint';
                break;

            case $flags & Column::SIZE_3B:
                $intType = 'mediumint';
                break;

            case $flags & Column::SIZE_8B:
                $intType = 'bigint';
                break;

        }
        return $intType;
    }

    protected function getFloatTypeString(Column $column)
    {
        if ($column->flags & Column::SIZE_8B) {
            return 'double';
        }
        return 'float';
    }

    protected function getStringTypeString(Column $column)
    {
        // TODO implement long strings

        $length = $column->stringLength ? $column->stringLength : 255;
        if ($column->stringFixed) {
            return 'char(' . $length . ')';
        } else {
            return 'varchar(' . $length . ')';
        }
    }

    protected function getTimestampTypeString(Column $column)
    {
        return 'timestamp';
    }


    protected function getBaseType(Column $column)
    {
        $flags = $column->flags;

        switch (true) {
            case ($flags & Column::INTEGER):
                $typeString = $this->getIntTypeString($column);
                break;

            case $flags & Column::FLOAT:
                $typeString = $this->getFloatTypeString($column);
                break;

            case $flags & Column::STRING:
                $typeString = $this->getStringTypeString($column);
                break;

            case $flags & Column::TIMESTAMP:
                $typeString = $this->getTimestampTypeString($column);
                break;

            default:
                throw new Exception('Undefined column type (' . $flags . ') for column ' . $column->propertyName, Exception::INVALID_SCHEMA);

        }

        return $typeString;

    }


    protected $overrideDefault = true;

    public function getByColumn(Column $column)
    {
        $flags = $column->flags;

        $typeString = $this->getBaseType($column);

        if ($flags & Column::UNSIGNED) {
            $typeString .= ' unsigned';
        }

        if ($flags & Column::NOT_NULL) {
            $typeString .= ' NOT NULL';
        }
        $default = $column->getDefault();
        if ($this->overrideDefault) {
            if ((false === $default)
                && ($column->flags & Column::NOT_NULL)
                && !($column->flags & Column::AUTO_ID)) {
                $isReflected = $column->flags & Column::IS_REFLECTED;
                if (!$isReflected) {
                    switch (true) {
                        case $column->flags & Column::STRING:
                            $default = '';
                            break;
                        case $column->flags & Column::INTEGER:
                            $default = 0;
                            break;
                        case $column->flags & Column::FLOAT:
                            $default = 0.0;
                            break;
                    }
                }
            }
        }

        if (false === $default && !($column->flags & Column::NOT_NULL)) {
            $isReflected = $column->flags & Column::IS_REFLECTED;
            if (!$isReflected) {
                $default = null;
            }
        }

        if (false !== $default) {
            if (is_int($default) || is_float($default)) {
                $default = (string)$default;
            }
            $typeString .= $this->database->expr(" DEFAULT ?", $default);
        }

        if ($column->flags & Column::AUTO_ID) {
            $typeString .= ' AUTO_INCREMENT';
        }

        return $typeString;
    }

}
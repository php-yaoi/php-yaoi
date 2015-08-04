<?php

namespace Yaoi\Database\Mysql;


use Yaoi\Database\Contract;
use Yaoi\Database\Definition\Column;
use Yaoi\Database\Exception;

class TypeString
{

    private $database;
    public function __construct(Contract $database) {
        $this->database = $database;
    }


    private function getIntTypeString(Column $column) {
        $intType = 'int';

        // TODO implement SIZE_ definitions
        /*
        switch (true) {
            case $flags & Column::SIZE_1B:
                $intType = 'tinyint';
                break;

            case $flags & Column::SIZE_2B:
                $intType = 'mediumint';
                break;

            case $flags & Column::SIZE_3B:
                $intType = 'mediumint';
                break;


        }
        */
        return $intType;
    }

    private function getFloatTypeString(Column $column) {
        // TODO implement double
        return 'float';
    }

    private function getStringTypeString(Column $column) {
        // TODO implement long strings

        $length = $column->stringLength ? $column->stringLength : 255;
        if ($column->stringFixed) {
            return 'char(' . $length . ')';
        }
        else {
            return 'varchar(' . $length . ')';
        }
    }

    private function getTimestampTypeString(Column $column) {
        return 'timestamp';
    }

    public function getByColumn(Column $column) {
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
                throw new Exception('Undefined column type for column ' . $column->propertyName, Exception::INVALID_SCHEMA);

        }

        if ($flags & Column::UNSIGNED) {
            $typeString .= ' unsigned';
        }

        if ($flags & Column::NOT_NULL) {
            $typeString .= ' NOT NULL';
        }

        if (false !== ($default = $column->getDefault())) {
            $typeString .= $this->database->expr(" DEFAULT ?", $default);
        }

        return $typeString;
    }

}
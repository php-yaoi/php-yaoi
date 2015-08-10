<?php

namespace Yaoi\Database\Utility;

use Yaoi\Database\Pgsql\AlterTable;
use Yaoi\Database\Pgsql\SchemaReader;
use Yaoi\Database\Pgsql\TypeString;
use Yaoi\Database\Utility;
use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Table;
use Yaoi\Database\Pgsql\CreateTable;

class Pgsql extends Utility
{
    /**
     * @param $tableName
     * @return Table
     */
    public function getTableDefinition($tableName)
    {
        $schemaReader = new SchemaReader($this->database);
        return $schemaReader->getTableDefinition($tableName);
    }


    public function generateCreateTableOnDefinition(Table $table) {
        $expression = new CreateTable();
        $expression->bindDatabase($this->database)->generate($table);
        return $expression->batch;
    }

    public function getColumnTypeString(Column $column)
    {
        $typeString = new TypeString($this->database);
        return $typeString->getByColumn($column);
    }


    /**
     * @inheritdoc
     */
    public function checkTable(Table $table)
    {
    }

    public function generateAlterTable(Table $before, Table $after)
    {
        $expression = new AlterTable();
        $expression->bindDatabase($this->database)->generate($before, $after);
        return $expression->batch;
    }

}
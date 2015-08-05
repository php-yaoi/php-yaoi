<?php

namespace Yaoi\Database\Utility;

use Yaoi\Database\Sqlite\SchemaReader;
use Yaoi\Database\Sqlite\TypeString;
use Yaoi\Database\Utility;
use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Table;
use Yaoi\Database\Sqlite\CreateTable;

class Sqlite extends Utility
{

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


    public function generateCreateTableOnDefinition(Table $table) {
        $expression = new CreateTable();
        $expression = $expression->bindDatabase($this->database)->generate($table);
        return $expression->batch;
    }

    /**
     * @param $tableName
     * @return Table
     */
    public function getTableDefinition($tableName)
    {
        $schemaReader = new SchemaReader($this->database);
        $definition = $schemaReader->getTableDefinition($tableName);
        return $definition;
    }



}
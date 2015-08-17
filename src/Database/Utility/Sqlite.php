<?php

namespace Yaoi\Database\Utility;

use Yaoi\Database\Sqlite\AlterTable;
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
        return new CreateTable($table);
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


    public function generateAlterTable(Table $before, Table $after)
    {
        return new AlterTable($before, $after);
    }

    public function tableExists($tableName)
    {
        $rows = $this->database
            ->query("SELECT name FROM sqlite_master WHERE type='table' AND name=?;", $tableName)->fetchAll();
        return (bool)$rows;
    }
}
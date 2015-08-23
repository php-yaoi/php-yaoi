<?php

namespace Yaoi\Database\Pgsql;

use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Table;

class Utility extends \Yaoi\Database\Utility
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
        return new CreateTable($table);
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
        return new AlterTable($before, $after);
    }

    public function tableExists($tableName)
    {
        $rows = $this->database->query("SELECT 1
   FROM   information_schema.tables
   WHERE  table_schema = ?
   AND    table_name = ?", $this->database->settings->path, $tableName)->fetchAll();
        return (bool)$rows;
    }


}
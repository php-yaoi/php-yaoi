<?php

namespace Yaoi\Database\Pgsql;

use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Table;
use Yaoi\Log;

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
    public function checkColumn(Column $table)
    {
    }

    public function generateAlterTable(Table $before, Table $after)
    {
        return new AlterTable($before, $after);
    }

    public function tableExists($tableName)
    {
        $rows = $this->database->select()
            ->select('/* table exists check */ 1')
            ->from('information_schema.tables')
            ->where('table_catalog = ?', $this->database->getSchemaName())
            ->where('table_name = ?', $tableName)
            ->query()
            ->fetchRow();
        return (bool)$rows;
    }


}
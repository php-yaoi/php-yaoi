<?php

namespace Yaoi\Database\Utility;

use Yaoi\Database\Mysql\SchemaReader;
use Yaoi\Database\Mysql\TypeString;
use Yaoi\Database\Utility;
use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Table;
use Yaoi\Sql\CreateTable;

class Mysql extends Utility
{
    public function killSleepers($timeout = 30)
    {
        foreach ($this->database->query("SHOW PROCESSLIST") as $row) {
            if ($row['Time'] > $timeout) {
                $this->database->query("KILL $row[Id]");
            }
        }
        return $this;
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


    public function getColumnTypeString(Column $column)
    {
        $typeString = new TypeString($this->database);
        return $typeString->getByColumn($column);
    }

    /**
     * @param Column[] $columns
     * @return mixed
     */
    public function checkColumns(array $columns)
    {
        foreach ($columns as $column) {
            if ($column->flags & Column::TIMESTAMP) {
                if (!$column->getDefault()) {
                    $column->setDefault('0000-00-00 00:00:00');
                    $column->setFlag(Column::NOT_NULL);
                }
            }
        }
    }


    public function generateCreateTableOnDefinition(Table $table) {
        $expression = new CreateTable();
        $expression = $expression->bindDatabase($this->database)->generate($table);
        return $expression;
    }

}
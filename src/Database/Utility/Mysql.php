<?php

namespace Yaoi\Database\Utility;

use Yaoi\Database\Mysql\CreateTable;
use Yaoi\Database\Mysql\SchemaReader;
use Yaoi\Database\Mysql\TypeString;
use Yaoi\Database\Utility;
use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Table;

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
     * @inheritdoc
     */
    public function checkTable(Table $table)
    {
        foreach ($table->getColumns(true) as $column) {
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
<?php

namespace Yaoi\Database\Utility;

use Yaoi\Database\Contract as DatabaseContract;
use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Table;
use Yaoi\Sql\CreateTable;

interface Contract
{
    public function setDatabase(DatabaseContract $database);

    /**
     * @param $tableName
     * @return Table
     */
    public function getTableDefinition($tableName);

    /**
     * @param Table $table
     * @return CreateTable
     */
    public function generateCreateTableOnDefinition(Table $table);

    public function generateAlterTable(Table $before, Table $after);

    public function getColumnTypeString(Column $column);

    public function dropTableIfExists($tableName);
    public function dropTable($tableName);

}
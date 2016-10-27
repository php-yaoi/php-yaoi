<?php

namespace Yaoi\Database\Utility;

use Yaoi\Database;
use Yaoi\Database\Definition;
use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Table;
use Yaoi\Sql\AlterTable;
use Yaoi\Sql\CreateTable;

interface Contract
{
    public function setDatabase(Database $database);

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

    /**
     * @param Table $before
     * @param Table $after
     * @return AlterTable
     */
    public function generateAlterTable(Table $before, Table $after);

    public function generateForeignKeyExpression(Definition\ForeignKey $foreignKey);

    public function getColumnTypeString(Column $column);

    public function dropTableIfExists($tableName);
    public function dropTable($tableName);

    public function generateDropTable($tableName);
    public function generateDropForeignKeys($tableName);
    public function tableExists($tableName);


    /**
     * Check/fix database related type misconceptions
     *
     * @param Column $table
     */
    public function checkColumn(Column $table);

}
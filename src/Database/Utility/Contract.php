<?php

namespace Yaoi\Database\Utility;

use Yaoi\Database\Contract as DatabaseContract;
use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Table;

interface Contract
{
    public function setDatabase(DatabaseContract $database);

    /**
     * @param $tableName
     * @return Table
     */
    public function getTableDefinition($tableName);

    public function generateCreateTableOnDefinition(Table $table);

    public function getColumnTypeString(Column $column);
}
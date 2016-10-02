<?php

namespace Yaoi\Database\Entity;

use Yaoi\Database\Definition\Columns;
use Yaoi\Database\Definition\Table;

interface Contract
{
    /**
     * Required setup column types in provided columns object
     * @param $columns static|Columns
     */
    static function setUpColumns($columns);

    /**
     * Optional setup table indexes and other properties, can be left empty
     * @param Table $table
     * @param static|Columns $columns
     * @return void
     */
    static function setUpTable(\Yaoi\Database\Definition\Table $table, $columns);
}
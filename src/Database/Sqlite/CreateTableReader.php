<?php

namespace Yaoi\Database\Sqlite;


use Yaoi\String\Parser;

class CreateTableReader extends \Yaoi\Database\Mysql\CreateTableReader
{
    protected function isAutoId(Parser $column)
    {
        return $column->contain('AUTOINCREMENT');
    }
}
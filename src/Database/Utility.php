<?php

namespace Yaoi\Database;

use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Table;
use Yaoi\Database\Utility\Contract as UtilityContract;
use Yaoi\BaseClass;
use Yaoi\Database;
use Yaoi\Sql\AlterTable;
use Yaoi\Sql\CreateTable;
use Yaoi\Sql\Symbol;

abstract class Utility extends BaseClass implements UtilityContract
{
    /**
     * @var Database
     */
    protected $database;

    /**
     * @param Database $database
     * @return $this
     */
    public function setDatabase(Database $database)
    {
        $this->database = $database;
        return $this;
    }


    public function dropTableIfExists($tableName)
    {
        $this->database->query("DROP TABLE IF EXISTS ?", new Symbol($tableName));
    }

    public function dropTable($tableName)
    {
        $this->database->query("DROP TABLE ?", new Symbol($tableName));
    }
}

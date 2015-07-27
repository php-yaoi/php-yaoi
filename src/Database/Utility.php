<?php

namespace Yaoi\Database;

use Yaoi\Database\Definition\Column;
use Yaoi\Database\Utility\Contract as UtilityContract;
use Yaoi\BaseClass;
use Yaoi\Database\Contract as DatabaseContract;

abstract class Utility extends BaseClass implements UtilityContract
{
    /**
     * @var DatabaseContract
     */
    protected $database;

    /**
     * @param Contract $database
     * @return $this
     */
    public function setDatabase(DatabaseContract $database)
    {
        $this->database = $database;
        return $this;
    }


    /**
     * @param Column[] $columns
     * @return mixed
     */
    abstract public function checkColumns(array $columns);

}

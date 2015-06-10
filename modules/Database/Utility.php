<?php

namespace Yaoi\Database;

use Yaoi\Database\Utility\Contract as UtilityContract;
use Yaoi\BaseClass;
use Yaoi\Database\Contract as DatabaseContract;

abstract class Utility extends BaseClass implements UtilityContract
{
    /**
     * @var DatabaseContract
     */
    protected $db;

    public function setDatabase(DatabaseContract $db)
    {
        $this->db = $db;
    }

}
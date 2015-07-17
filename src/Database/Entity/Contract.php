<?php

namespace Yaoi\Database\Entity;

use Yaoi\Database\Definition\Table;

interface Contract
{
    /**
     * Setup column types in provided columns object
     * @param $columns static|\stdClass
     */
    static function setUpColumns($columns);

}
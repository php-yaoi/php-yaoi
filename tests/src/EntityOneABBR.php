<?php

namespace YaoiTests;


use Yaoi\Database\Definition\Column;
use Yaoi\Database\Entity;

class EntityOneABBR extends Entity
{
    public $id;
    public $name;
    public $address;
    public $createdAt;


    /**
     * Setup column types in provided columns object
     * @param $columns static|\stdClass
     */
    static function setUpColumns($columns)
    {
        $columns->id = Column::AUTO_ID;
        $columns->name = Column::create(Column::STRING + Column::NOT_NULL)->setUnique();
        $columns->address = Column::create(Column::STRING)->setDefault('');
        $columns->createdAt = Column::TIMESTAMP;
    }
}
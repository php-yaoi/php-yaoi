<?php

namespace YaoiTests;

use Yaoi\Database\Definition\Column;
use Yaoi\Database\Entity;

class EntityTwo extends Entity
{
    public $id;
    public $oneId;
    public $createdAt;
    public $updatedAt;
    public $info;

    protected static $tableName = 'custom_name';

    /**
     * Setup column types in provided columns object
     * @param $columns static|\stdClass
     */
    static function setUpColumns($columns)
    {
        $columns->id = Column::AUTO_ID;
        $columns->oneId = EntityOneABBR::columns()->id;
        $columns->createdAt = Column::TIMESTAMP;
        $columns->updatedAt = Column::TIMESTAMP;
        $columns->info = Column::create(Column::STRING)->setIndexed();
    }

}
<?php

namespace YaoiTests;

use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Table;
use Yaoi\Database\Entity;

class EntityTwo extends Entity
{
    public $id;
    public $oneId;
    public $createdAt;
    public $updatedAt;
    public $info;

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

    /**
     * Optional setup table indexes and other properties, can be left empty
     * @param Table $table
     * @return void
     */
    static function setUpTable(\Yaoi\Database\Definition\Table $table)
    {
        $table->setSchemaName('custom_name');
    }


}
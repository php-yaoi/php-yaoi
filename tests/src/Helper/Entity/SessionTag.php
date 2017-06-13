<?php
namespace YaoiTests\Helper\Entity;


use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Table;
use Yaoi\Database\Entity;

class SessionTag extends Entity
{
    public $sessionId;
    public $tagId;
    public $addedAtUt;

    /**
     * Required setup column types in provided columns object
     * @param $columns static|\stdClass
     */
    static function setUpColumns($columns)
    {
        $columns->sessionId = Session::columns()->id;
        $columns->tagId = Tag::columns()->id;
        $columns->addedAtUt = Column::INTEGER + Column::NOT_NULL;
    }

    /**
     * Optional setup table indexes and other properties, can be left empty
     * @param Table $table
     * @param static|\stdClass $columns
     * @return void
     */
    static function setUpTable(\Yaoi\Database\Definition\Table $table, $columns)
    {
        $table
            ->setSchemaName('yaoi_tests_entity_session_tag')
            ->disableDatabaseForeignKeys()
            ->setPrimaryKey($columns->sessionId, $columns->tagId);
    }


}
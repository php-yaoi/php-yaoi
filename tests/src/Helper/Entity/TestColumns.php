<?php
namespace YaoiTests\Helper\Entity;

use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Table;
use Yaoi\Database\Entity;

class TestColumns extends Entity
{
    public $id;
    public $intColumn;
    public $int8Column;
    public $floatColumn;
    public $stringColumn;

    /**
     * Required setup column types in provided columns object
     * @param $columns static|\stdClass
     */
    static function setUpColumns($columns)
    {
        $columns->id = Column::AUTO_ID;
        $columns->intColumn = Column::create(Column::INTEGER + Column::NOT_NULL)->setDefault('2');
        $columns->int8Column = Column::create(Column::INTEGER + Column::NOT_NULL + Column::SIZE_8B)->setDefault(2);
        $columns->floatColumn = Column::create(Column::FLOAT + Column::NOT_NULL)->setDefault(1.33);
        $columns->stringColumn = Column::create(Column::STRING + Column::NOT_NULL)->setDefault(11);
    }

    /**
     * Optional setup table indexes and other properties, can be left empty
     * @param Table $table
     * @param static|\stdClass $columns
     * @return void
     */
    static function setUpTable(\Yaoi\Database\Definition\Table $table, $columns)
    {
        $table->schemaName = 'test_columns';
    }

}

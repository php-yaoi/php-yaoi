<?php
namespace YaoiTests\DatabaseEntity;

use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Table;
use YaoiTests\EntityOneABBR;
use YaoiTests\EntityTwo;

abstract class TestCase extends \Yaoi\Test\PHPUnit\TestCase
{
    /** @var  \Yaoi\Database */
    protected $database;

    /**
     * By default column has STRING type
     * @see Column::__construct
     */
    public function testDefaultColumn() {
        $this->assertSame(Column::STRING, Column::create()->flags);
    }


    /**
     * AUTO_ID column is INTEGER by default
     * @see Column::__construct
     */
    public function testAutoIdColumn() {
        $this->assertSame(Column::AUTO_ID + Column::INTEGER, Column::create(Column::AUTO_ID)->flags);
        $this->assertSame(Column::AUTO_ID + Column::INTEGER + Column::SIZE_4B,
            Column::create(Column::AUTO_ID + Column::INTEGER + Column::SIZE_4B)->flags);
    }

    /**
     * AUTO_ID column is primary key
     * @see Column::__construct
     * @todo throw exception on multiple AUTO_ID and setting custom PK when AUTO_ID is set
     */
    public function testAutoIdPrimary() {
        $columns = new \stdClass();
        $columns->id = new Column(Column::AUTO_ID);

        $table = new Table($columns);
        $this->assertSame(array('id' => $columns->id), $table->primaryKey);
    }


    public function testColumns() {
        $table = EntityOneABBR::table();
        $columnsFlags = array();
        foreach ($table->getColumns(true) as $column) {
            $columnsFlags[$column->propertyName] = $column->flags;
        }

        $this->assertArrayBitwiseAnd(array(
            'id' => Column::AUTO_ID + Column::INTEGER,
            'name' => Column::STRING + Column::NOT_NULL,
            'address' => Column::STRING,
            'createdAt' => Column::TIMESTAMP,
        ), $columnsFlags);
    }


    /**
     * @see Yaoi\Database\Definition\Table::schemaName
     */
    public function testSchemaName() {
        $this->assertSame('yaoi_tests_entity_one_abbr', EntityOneABBR::table()->schemaName);
        $this->assertSame('custom_name', EntityTwo::table()->schemaName);
    }

    /**
     * @see Yaoi\Database\Definition\Table::schemaName
     */
    public function testClassName() {
        $this->assertSame('YaoiTests\EntityOneABBR', EntityOneABBR::table()->className);
        $this->assertSame('YaoiTests\EntityTwo', EntityTwo::table()->className);
    }


    protected $entityOneCreateTableExpected;
    protected $entityTwoCreateTableExpected;


    public function testCreateTable() {
        $this->assertSame(
            $this->entityOneCreateTableExpected,
            $this->database->getUtility()
                ->generateCreateTableOnDefinition(EntityOneABBR::table()));

        $this->assertSame(
            $this->entityTwoCreateTableExpected,
            $this->database->getUtility()
                ->generateCreateTableOnDefinition(EntityTwo::table()));

    }




}
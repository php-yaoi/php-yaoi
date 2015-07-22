<?php
use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Table;
use Yaoi\Database\Entity;
use Yaoi\Test\PHPUnit\TestCase;

class TestEntityDatabaseUnified extends TestCase  {

    public function setUp() {
        $this->markTestSkipped('Test is deprecated');
    }


    public function testDefinition() {
        $table = TestEntityDb::table();

        $this->assertSame(array(
            0 => 'id',
            1 => 'name',
            2 => 'age',
            3 => 'weight',
            4 => 'url',
            5 => 'birthDate',
        ), array_keys($table->getColumns(true)));


        $this->assertSame('id', $table->autoIdColumn->schemaName);

        $this->assertSame('test_entity_db', $table->schemaName);
    }

    public function testSave() {
        $item = new TestEntityDb();
        $item->name = 'Dick Cocker';
        $item->age = 32;
        $item->weight = 78;
        $item->url = 'http://veadev.tk';
        $item->birthDate = '1983-04-10';
        $this->assertSame(null, $item->id);

        $item->save();
        $this->assertEquals(1, $item->id);

        $this->assertSame($item->name, TestEntityDb::find($item->id)->name);

        $item->name = 'John Doe';

        $item->save();
        $this->assertSame($item->name, TestEntityDb::find($item->id)->name);

    }

}

/**
 * Class TestEntityDB
 */
class TestEntityDb extends Entity
{
    public $id;
    public $name;
    public $age;
    public $weight;
    public $url;
    public $birthDate;

    /**
     * Setup column types in provided columns object
     * @param $columns static|\stdClass
     */
    static function setUpColumns($columns)
    {
        $columns->id = Column::AUTO_ID;
        $columns->name = Column::create()->setStringLength(15, true);
        $columns->age = Column::INTEGER;
        $columns->weight = Column::INTEGER + Column::UNSIGNED;
        $columns->url = Column::STRING;
        $columns->birthDate = Column::TIMESTAMP;
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: vearutop
 * Date: 29.09.2015
 * Time: 14:58
 */
namespace YaoiTests\PHPUnit\Entity;

use Yaoi\Test\PHPUnit\TestCase;
use Yaoi\Undefined;
use YaoiTests\Helper\Entity\TestEntityDB;

class TestEntityDatabaseUnified extends TestCase
{

    public function setUp()
    {
        $this->markTestSkipped('Test is deprecated');
    }


    public function testDefinition()
    {
        $table = TestEntityDB::table();

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

    public function testSave()
    {
        $item = new TestEntityDB();
        $item->name = 'Dick Cocker';
        $item->age = 32;
        $item->weight = 78;
        $item->url = 'http://veadev.tk';
        $item->birthDate = '1983-04-10';
        $this->assertInstanceOf(get_class(Undefined::get()), $item->id);

        $item->save();
        $this->assertEquals(1, $item->id);

        $this->assertSame($item->name, TestEntityDB::findByPrimaryKey($item->id)->name);

        $item->name = 'John Doe';

        $item->save();
        $this->assertSame($item->name, TestEntityDB::findByPrimaryKey($item->id)->name);

    }

}
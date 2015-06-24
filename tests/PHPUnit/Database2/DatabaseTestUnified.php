<?php
use Yaoi\Database\Contract;
use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Table;
use Yaoi\Sql\Symbol;
use Yaoi\Test\PHPUnit\TestCase;

abstract class DatabaseTestUnified extends TestCase {
    /** @var  Contract */
    protected $db;

    protected $skip;

    protected $createTable1 = <<<SQL
CREATE TABLE IF NOT EXISTS test_def (
  id1 int NOT NULL AUTO_INCREMENT,
  id2 int NOT NULL,
  name VARCHAR(10) NOT NULL DEFAULT 'Jon Snow',
  address CHAR(10) DEFAULT NULL,
  PRIMARY KEY (id1, id2)/*,
  KEY (name),
  UNIQUE KEY (id1, name),
  UNIQUE KEY (id2, address)*/
);
SQL;


    protected function initDef() {
        $this->db->query("DROP TABLE IF EXISTS ?", new Symbol('test_def'));
        $this->db->query($this->createTable1);
    }


    protected function columnsTest(Table $def) {
        $this->assertSame(array('id1', 'id2', 'name', 'address'), array_keys($def->columns));
    }

    protected function autoIncrementTest(Table $def) {
        $this->assertSame('id1', $def->autoIncrement);
    }

    protected function notNullTest(Table $def)
    {
        $this->assertSame(array(
            'id1' => true,
            'id2' => true,
            'name' => true,
            'address' => false,
        ), $def->notNull);
    }

    protected function primaryTest(Table $def) {
        $this->assertSame(array('id1' => 'id1', 'id2' => 'id2'), $def->primaryKey);
    }

    public function testDescribe() {
        if ($this->skip) {
            return;
        }

        $this->initDef();

        $def = $this->db->getTableDefinition('test_def');
        $this->columnsTest($def);
        $this->autoIncrementTest($def);
        $this->notNullTest($def);
        $this->primaryTest($def);
        //print_r($def);

    }



    protected $createTable2 = <<<SQL
CREATE TABLE IF NOT EXISTS test_columns (
a1 int,
a2 tinyint,
a3 smallint,
a4 mediumint,
a5 bigint,
a6 decimal(1,1),
a7 numeric(1,1),
a8 float,
a9 real,
a10 double,
a11 text,
a12 char(10),
a13 VARCHAR(255),
a14 TIMESTAMP,
a15 date,
a16 datetime,
a17 time
)
SQL;

    protected function columnsTest2(Table $def) {
        $this->assertArrayBitwiseAnd(array(
            'a1' => Column::INTEGER,
            'a2' => Column::INTEGER,
            'a3' => Column::INTEGER,
            'a4' => Column::INTEGER,
            'a5' => Column::INTEGER,
            'a6' => Column::FLOAT,
            'a7' => Column::FLOAT,
            'a8' => Column::FLOAT,
            'a9' => Column::FLOAT,
            'a10' => Column::FLOAT,
            'a11' => Column::STRING,
            'a12' => Column::STRING,
            'a13' => Column::STRING,
            'a14' => Column::TIMESTAMP,
            'a15' => Column::TIMESTAMP,
            'a16' => Column::TIMESTAMP,
            'a17' => Column::STRING
        ), $def->columns);
    }

    protected function initDef2() {
        $this->db->query("DROP TABLE IF EXISTS ?", new Symbol('test_columns'));
        $this->db->query($this->createTable2);
    }


    public function testDescribe2() {
        if ($this->skip) {
            return;
        }

        $this->initDef2();
        $def = $this->db->getTableDefinition('test_columns');
        $this->columnsTest2($def);
    }

}
<?php

namespace YaoiTests\PHPUnit\Database\Mysql;


use Yaoi\Database;
use Yaoi\Test\PHPUnit\TestCase;
use YaoiTests\Helper\Database\CheckAvailable;

class CreateTableReaderTest extends TestCase
{
    /**
     * @var Database
     */
    protected $database;

    public function setUp()
    {
        $this->database = CheckAvailable::getMysqli();
    }

    protected $createTableStatement = "CREATE TABLE `test_indexes` (
 `id` int NOT NULL AUTO_INCREMENT,
 `name` varchar(255) NOT NULL,
 `uni_one` int DEFAULT NULL,
 `uni_two` int DEFAULT NULL,
 `default_null` float DEFAULT NULL,
 `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
 `ref_id` int NOT NULL,
 `r_one` int DEFAULT NULL,
 `r_two` int DEFAULT NULL,
 UNIQUE KEY `unique_uni_one_uni_two` (`uni_one`, `uni_two`),
 KEY `key_name` (`name`),
 CONSTRAINT `fk_test_indexes_ref_id_table_a_id` FOREIGN KEY (`ref_id`) REFERENCES `table_a` (`id`),
 CONSTRAINT `fk_test_indexes_r_one_r_two_table_a_m_one_table_a_m_two` FOREIGN KEY (`r_one`, `r_two`) REFERENCES `table_a` (`m_one`, `m_two`),
 PRIMARY KEY (`id`)
)";

    public function testColumns() {
        $definition = Database\Mysql\CreateTableReader::create($this->createTableStatement, $this->database)->getDefinition();
        $this->assertSame($this->createTableStatement, (string)$this->database->getUtility()->generateCreateTableOnDefinition($definition));
    }
}
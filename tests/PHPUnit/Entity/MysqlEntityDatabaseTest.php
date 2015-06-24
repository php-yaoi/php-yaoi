<?php

use Yaoi\Database;

require_once __DIR__ . '/TestEntityDatabaseUnified.php';

class MysqlEntityDatabaseTest extends TestEntityDatabaseUnified {

    public function setUp() {
        $db = Database::getInstance('test_mysqli');

        $createSQL = <<<SQL
CREATE TABLE test_entity (
`id` integer unsigned auto_increment,
`name` varchar(255),
`age` tinyint,
`weight` tinyint,
`url` varchar(255),
`birthDate` timestamp,
PRIMARY KEY(`id`))
SQL;

        $db->query("DROP TABLE IF EXISTS `test_entity`");
        $db->query($createSQL);

        TestEntityDB::definition()->bindDatabase($db);
    }
}
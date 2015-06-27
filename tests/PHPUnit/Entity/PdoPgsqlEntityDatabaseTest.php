<?php

use Yaoi\Database;

require_once __DIR__ . '/TestEntityDatabaseUnified.php';

class PdoPgsqlEntityDatabaseTest extends TestEntityDatabaseUnified {

    public function setUp() {
        if (extension_loaded('PDO')) {
            $drivers = pdo_drivers();
            if (!in_array('pgsql', $drivers)) {
                $this->markTestSkipped('PDO pgsql driver is not available.');
                return;
            }
        }
        else {
            $this->markTestSkipped('PDO extension is not available.');
            return;
        }

        $db = Database::getInstance('test_pdo_pgsql');

        $createSQL = <<<SQL
CREATE TABLE test_entity (
"id" SERIAL,
"name" varchar(255),
"age" int,
"weight" int,
"url" varchar(255),
"birthDate" timestamp,
PRIMARY KEY("id")
);
SQL;

        $db->query("DROP TABLE IF EXISTS test_entity");
        $db->query($createSQL);

        TestEntityDB::definition()->bindDatabase($db);
    }
}
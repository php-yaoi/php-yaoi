<?php

use Yaoi\Database;

require_once __DIR__ . '/TestEntityDatabaseUnified.php';

class PgsqlEntityDatabaseTest extends TestEntityDatabaseUnified {

    public function setUp() {
        if (!function_exists('pg_connect')) {
            $this->markTestSkipped('pg_connect is not available.');
            return;
        }

        $db = Database::getInstance('test_pgsql');

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
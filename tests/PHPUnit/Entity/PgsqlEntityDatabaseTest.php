<?php

use Yaoi\Database;

require_once __DIR__ . '/TestEntityDatabaseUnified.php';

class PgsqlEntityDatabaseTest extends TestEntityDatabaseUnified {

    public function setUp() {
        //$this->markTestSkipped('Test is deprecated');


        if (!function_exists('pg_connect')) {
            $this->markTestSkipped('pg_connect is not available.');
            return;
        }

        $db = Database::getInstance('test_pgsql');

        $createSQL = <<<SQL
CREATE TABLE test_entity_db (
"id" SERIAL,
"name" varchar(255),
"age" int,
"weight" int,
"url" varchar(255),
"birth_date" timestamp,
PRIMARY KEY("id")
);
SQL;

        $db->query("DROP TABLE IF EXISTS test_entity_db");
        $db->query($createSQL);

        TestEntityDb::table()->bindDatabase($db);
    }
}
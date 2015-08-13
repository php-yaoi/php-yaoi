<?php

use Yaoi\Database;

require_once __DIR__ . '/TestEntityDatabaseUnified.php';

class PdoPgsqlEntityDatabaseTest extends TestEntityDatabaseUnified {

    public function setUp() {
        \YaoiTests\Database\CheckAvailable::checkPdoPgsql();
        $db = Database::getInstance('test_pdo_pgsql');

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

        TestEntityDb::bindDatabase($db);
    }
}
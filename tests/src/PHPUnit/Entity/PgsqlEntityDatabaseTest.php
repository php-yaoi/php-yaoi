<?php

namespace YaoiTests\PHPUnit\Entity;

use Yaoi\Database;
use YaoiTests\Helper\Entity\TestEntityDB;

class PgsqlEntityDatabaseTest extends TestEntityDatabaseUnified
{

    public function setUp()
    {
        \YaoiTests\Helper\Database\CheckAvailable::getPgsql();
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

        TestEntityDB::bindDatabase($db);
    }
}
<?php

use Yaoi\Database;

require_once __DIR__ . '/TestEntityDatabaseUnified.php';

class PdoPgsqlEntityDatabaseTest extends TestEntityDatabaseUnified {

    public function setUp() {
        //$this->markTestSkipped('Test is deprecated');

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

        try {
            $db = Database::getInstance('test_pdo_pgsql');
        }
        catch (\Yaoi\Service\Exception $exception) {
            $this->markTestSkipped($exception->getMessage());
            return;
        }

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
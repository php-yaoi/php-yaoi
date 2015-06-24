<?php

use Yaoi\Database;

require_once __DIR__ . '/DatabaseTestUnified.php';

class DatabasePgsqlTest extends DatabaseTestUnified {

    protected $createTable1 = <<<SQL
CREATE TABLE IF NOT EXISTS test_def (
  id1 SERIAL,
  id2 int NOT NULL,
  name VARCHAR(10) NOT NULL DEFAULT 'Jon Snow',
  address CHAR(10) DEFAULT NULL,
  PRIMARY KEY (id1, id2)
);
SQL;


    protected function setUp()
    {
        if (!function_exists('pg_connect')) {
            $this->markTestSkipped('pg_connect is not available.');
        }
    }

    public function __construct() {
        $this->db = Database::getInstance('test_pgsql');
    }


    protected $createTable2 = <<<SQL
CREATE TABLE IF NOT EXISTS test_columns (
a1 int,
a2 int,
a3 smallint,
a4 int,
a5 bigint,
a6 decimal(1,1),
a7 numeric(1,1),
a8 float,
a9 real,
a10 real,
a11 text,
a12 char(10),
a13 VARCHAR(255),
a14 TIMESTAMP,
a15 date,
a16 timestamp,
a17 time
)
SQL;

}
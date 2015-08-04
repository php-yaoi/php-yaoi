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
        try {
            $this->db = Database::getInstance('test_pgsql');
        }
        catch (\Yaoi\Service\Exception $exception) {
            $this->markTestSkipped($exception->getMessage());
        }

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


    protected $createTableStatement = <<<SQL
CREATE TABLE test_indexes (
 id int NOT NULL AUTO_INCREMENT,
 name varchar(255) NOT NULL,
 uni_one int DEFAULT NULL,
 uni_two int DEFAULT NULL,
 default_null float DEFAULT NULL,
 updated timestamp,
 ref_id int NOT NULL,
 r_one varchar(255) DEFAULT NULL,
 r_two varchar(255) DEFAULT NULL,
 UNIQUE KEY unique_uni_one_uni_two (uni_one, uni_two),
 KEY key_name (name),
 CONSTRAINT fk_test_indexes_ref_id_table_a_id FOREIGN KEY (ref_id) REFERENCES table_a (id),
 CONSTRAINT fk_test_indexes_r_one_r_two_table_a_m_one_m_two FOREIGN KEY (r_one, r_two) REFERENCES table_a (m_one, m_two),
 PRIMARY KEY (id)
)
SQL;


}
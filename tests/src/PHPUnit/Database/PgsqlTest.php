<?php

namespace YaoiTests\PHPUnit\Database;

use Yaoi\Database;


class PgsqlTest extends TestUnified
{

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
        \YaoiTests\Helper\Database\CheckAvailable::getPgsql();
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


    protected $createTableStatement = <<<SQL
CREATE TABLE "test_indexes" (
 "id" SERIAL,
 "name" varchar(255) NOT NULL,
 "uni_one" int,
 "uni_two" int,
 "default_null" float,
 "updated" timestamp,
 "ref_id" int NOT NULL,
 "r_one" int,
 "r_two" int,
 CONSTRAINT "unique_uni_one_uni_two" UNIQUE ("uni_one", "uni_two"),
 CONSTRAINT "fk_test_indexes_ref_id_table_a_id" FOREIGN KEY ("ref_id") REFERENCES "table_a" ("id"),
 CONSTRAINT "fk_test_indexes_r_one_r_two_table_a_m_one_table_a_m_two" FOREIGN KEY ("r_one", "r_two") REFERENCES "table_a" ("m_one", "m_two"),
 PRIMARY KEY ("id")
);
CREATE INDEX "key_name" ON "test_indexes" ("name");

SQL;


    protected $testCreateIndexesAlterExpected = <<<SQL
ALTER TABLE "test_indexes"
ADD COLUMN "new_field" char(15) NOT NULL DEFAULT 'normal',
DROP CONSTRAINT "unique_uni_one_uni_two";
CREATE UNIQUE INDEX "unique_updated" ON "test_indexes" ("updated");
DROP INDEX "key_name";

SQL;


    protected $testCreateTableAfterAlter = <<<SQL
CREATE TABLE "test_indexes" (
 "id" SERIAL,
 "name" varchar(255) NOT NULL,
 "uni_one" int,
 "uni_two" int,
 "default_null" float,
 "updated" timestamp,
 "new_field" varchar(255) NOT NULL DEFAULT 'normal',
 CONSTRAINT "unique_updated" UNIQUE ("updated"),
 PRIMARY KEY ("id")
)
SQL;


    protected $testDefaultValueConsistency = <<<LOG
Apply, table test_columns (YaoiTests\Helper\Entity\TestColumns) requires migration
CREATE TABLE "test_columns" (
 "id" SERIAL,
 "int_column" int NOT NULL DEFAULT 2,
 "int8_column" int NOT NULL DEFAULT 2,
 "float_column" float NOT NULL DEFAULT 1.33,
 "string_column" varchar(255) NOT NULL DEFAULT '11',
 PRIMARY KEY ("id")
)
OK
Apply, table test_columns (YaoiTests\Helper\Entity\TestColumns) is up to date

LOG;


}
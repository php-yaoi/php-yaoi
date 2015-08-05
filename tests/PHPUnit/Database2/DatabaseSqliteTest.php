<?php

use Yaoi\Database;
use Yaoi\Database\Definition\Table;

require_once __DIR__ . '/DatabaseTestUnified.php';
class DatabaseSqliteTest extends DatabaseTestUnified {
    protected $createTable1 = <<<SQL
CREATE TABLE IF NOT EXISTS test_def (
  id1 INTEGER NOT NULL,
  id2 INTEGER NOT NULL DEFAULT 1,
  name VARCHAR(10) NOT NULL DEFAULT 'Jon Snow',
  address CHAR(10),
  PRIMARY KEY(id1, id2)
);
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

    protected function autoIncrementTest(Table $def) {
        // TODO: support autoincrement
    }


    public function setUp() {
        $fileName = sys_get_temp_dir() . '/test-sqlite.sqlite';
        if (file_exists($fileName)) {
            unlink($fileName);
        }
        $this->db = new Database('sqlite:///' . $fileName);
    }


    protected function columnsTest2(Table $def) {
        return;
    }


    protected $testCreateIndexesAlterExpected = <<<SQL
ALTER TABLE `test_indexes`
ADD COLUMN `new_field` char(15) NOT NULL DEFAULT 'normal',
DROP INDEX `unique_uni_one_uni_two`,
DROP INDEX `key_name`
SQL;


}
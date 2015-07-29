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

}
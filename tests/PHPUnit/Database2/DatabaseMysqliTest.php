<?php

use Yaoi\Database;

require_once __DIR__ . '/DatabaseTestUnified.php';
class DatabaseMysqliTest extends DatabaseTestUnified {
    public function setUp() {
        $this->db = Database::getInstance('test_mysqli');
    }

}
<?php

use Yaoi\Database;

require_once __DIR__ . '/DatabaseTestUnified.php';
class DatabaseMysqliTest extends DatabaseTestUnified {
    public function setUp() {
        try {
            $this->db = Database::getInstance('test_mysqli');
        }
        catch (\Yaoi\Service\Exception $exception) {
            $this->markTestSkipped($exception->getMessage());
        }

    }

}
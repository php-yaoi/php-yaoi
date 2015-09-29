<?php

namespace YaoiTests\PHPUnit\Database\Entity;

use Yaoi\Database;
use Yaoi\Test\PHPUnit\TestCase;
use YaoiTests\Helper\Entity\Host;
use YaoiTests\Helper\Entity\User;

class MysqlTest extends TestCase
{
    protected $database;

    public function setUp() {
        $this->database = Database::getInstance('test_mysqli');
        User::$revision = 1;
        User::bindDatabase($this->database);
    }

    public function testUpdateSchema() {


        echo User::table()->getCreateTable();
        $this->assertSame('', '');
    }
}
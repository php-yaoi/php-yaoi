<?php

namespace YaoiTests\PHPUnit\Database\Entity;

use Yaoi\Database;
use Yaoi\Log;
use Yaoi\Storage\PhpVar;
use Yaoi\Test\PHPUnit\TestCase;
use YaoiTests\Helper\Entity\User;

class MysqlComplexTest extends TestCase
{
    protected $database;

    public function setUp() {
        $this->database = Database::getInstance('test_mysqli');
    }


    public function testUpdateSchema() {
        $logStorage = new PhpVar();
        $log = Log::getInstance(function()use($logStorage){
            $settings = new Log\Settings();
            $settings->driverClassName = Log\Driver\Storage::className();
            $settings->castToString = true;
            $settings->storage = $logStorage;
            return $settings;
        });

        User::$revision = 1;
        User::bindDatabase($this->database, true);

        User::table()->migration()->rollback();

        User::table()->migration()->setLog($log)->apply();

        User::bindDatabase($this->database, true);
        User::table()->migration()->setLog($log)->apply();

        User::bindDatabase($this->database, true);
        User::table()->migration()->setLog($log)->apply();

        User::$revision = 2;
        User::bindDatabase($this->database, true);
        User::table()->migration()->setLog($log)->apply();

        User::bindDatabase($this->database, true);
        User::table()->migration()->setLog($log)->apply();

        User::$revision = 3;
        User::bindDatabase($this->database, true);
        User::table()->migration()->setLog($log)->apply();

        User::bindDatabase($this->database, true);
        User::table()->migration()->setLog($log)->apply();

        User::table()->migration()->setLog($log)->rollback();

        var_export($logStorage->exportArray());

    }
}
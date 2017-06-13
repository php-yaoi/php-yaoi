<?php

namespace YaoiTests\PHPUnit\Database\Entity\Migration;

use Yaoi\Database;
use Yaoi\Log;
use Yaoi\Test\PHPUnit\TestCase;
use YaoiTests\Helper\Database\CheckAvailable;
use YaoiTests\Helper\Entity\Host;
use YaoiTests\Helper\Entity\Session;
use YaoiTests\Helper\Entity\User;

abstract class BaseTest extends TestCase
{
    /** @var  Database */
    protected $database;

    public function setUp() {
        $this->database = CheckAvailable::getMysqli();
    }

    protected $expectedMigrationLog;

    public function testUpdateSchema() {
        $logString = '';
        $log = Log::getInstance(function()use(&$logString){
            $settings = new Log\Settings();
            $settings->driverClassName = Log\Driver\StringVar::className();
            $settings->storage = &$logString;
            return $settings;
        });
        //$log = new Log('stdout');
        //$this->database->log($log);

        User::$revision = 1;
        User::bindDatabase($this->database, true);
        Host::bindDatabase($this->database, true);
        Session::bindDatabase($this->database, true);

        //$this->database->log(new Log('colored-stdout'));

        // prepare dependencies
        User::migration()->rollback();
        Host::migration()->apply();
        Session::migration()->apply();

        Database\Entity\Migration::$enableStateCache = false;
        //$log = new Log('colored-stdout');

        $log->push('Table creation expected');
        User::migration()->setLog($log)->apply();

        $log->push('No action (up to date) expected');
        User::bindDatabase($this->database, true);
        User::migration()->setLog($log)->apply();

        $log->push('No action (up to date) expected');
        User::bindDatabase($this->database, true);
        User::migration()->setLog($log)->apply();

        $log->push('Table revision increased, added age, hostId');
        User::$revision = 2;
        User::bindDatabase($this->database, true);
        User::migration()->setLog($log)->apply();

        $log->push('No action (up to date) expected');
        User::bindDatabase($this->database, true);
        User::migration()->setLog($log)->apply();

        $log->push('Table revision increased, removed hostId, name, added sessionId, firstName, lastName');
        User::$revision = 3;
        User::bindDatabase($this->database, true);
        User::migration()->setLog($log)->apply();

        $log->push('No action (up to date) expected');
        User::bindDatabase($this->database, true);
        User::migration()->setLog($log)->apply();

        $log->push('Table removal expected');
        User::migration()->setLog($log)->rollback();
        User::bindDatabase($this->database, true);
        User::$revision = 1;

        $log->push('No action (is already non-existent) expected');
        User::bindDatabase($this->database, true);
        User::migration()->setLog($log)->rollback();


        $this->assertStringEqualsCRLF($this->expectedMigrationLog, $logString);

    }

}
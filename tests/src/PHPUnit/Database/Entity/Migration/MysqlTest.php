<?php

namespace YaoiTests\PHPUnit\Database\Entity\Migration;

use Yaoi\Database;
use Yaoi\Log;
use Yaoi\Test\PHPUnit\TestCase;
use YaoiTests\Helper\Database\CheckAvailable;
use YaoiTests\Helper\Entity\Host;
use YaoiTests\Helper\Entity\OneABBR;
use YaoiTests\Helper\Entity\Session;
use YaoiTests\Helper\Entity\User;

class MysqlTest extends TestCase
{
    /** @var  Database */
    protected $database;

    public function setUp() {
        $this->database = CheckAvailable::getMysqli();
    }

    protected $expectedMigrationLog = <<<EOD
Table creation expected
Apply, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) requires migration
CREATE TABLE `yaoi_tests_helper_entity_user` (
 `id` int NOT NULL AUTO_INCREMENT,
 `name` varchar(255) NOT NULL,
 PRIMARY KEY (`id`)
)
OK
No action (up to date) expected
Apply, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) is up to date
No action (up to date) expected
Apply, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) is up to date
Table revision increased, added age, hostId
Apply, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) requires migration
Dependent migration required
Apply, table yaoi_tests_entity_host (YaoiTests\Helper\Entity\Host) is up to date
ALTER TABLE `yaoi_tests_helper_entity_user`
ADD COLUMN `age` int DEFAULT NULL,
ADD COLUMN `host_id` int NOT NULL,
ADD INDEX `key_age` (`age`),
ADD CONSTRAINT `k432f6fb01e8766435a432e5ed8ffb2ef` FOREIGN KEY (`host_id`) REFERENCES `yaoi_tests_entity_host` (`id`)
OK
No action (up to date) expected
Apply, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) is up to date
Table revision increased, removed hostId, name, added sessionId, firstName, lastName
Apply, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) requires migration
Dependent migration required
Apply, table yaoi_tests_entity_session (YaoiTests\Helper\Entity\Session) is up to date
ALTER TABLE `yaoi_tests_helper_entity_user`
ADD COLUMN `session_id` int NOT NULL,
ADD COLUMN `first_name` varchar(255) NOT NULL,
ADD COLUMN `last_name` varchar(255) NOT NULL,
DROP COLUMN `name`,
DROP COLUMN `host_id`,
ADD UNIQUE INDEX `unique_last_name_first_name` (`last_name`, `first_name`),
DROP INDEX `k432f6fb01e8766435a432e5ed8ffb2ef`,
ADD CONSTRAINT `k42405537c0e04845e2902c8a7fb322be` FOREIGN KEY (`session_id`) REFERENCES `yaoi_tests_entity_session` (`id`),
DROP FOREIGN KEY `k432f6fb01e8766435a432e5ed8ffb2ef`
OK
No action (up to date) expected
Apply, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) is up to date
Table removal expected
Rollback, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) requires deletion
OK
No action (is already non-existent) expected
Rollback, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) is already non-existent

EOD;


    public function testUpdateSchema() {
        $logString = '';
        $log = Log::getInstance(function()use(&$logString){
            $settings = new Log\Settings();
            $settings->driverClassName = Log\Driver\StringVar::className();
            $settings->storage = &$logString;
            return $settings;
        });

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

    public function testDefaultAlter()
    {
        $logString = '';
        $log = Log::getInstance(function()use(&$logString){
            $settings = new Log\Settings();
            $settings->driverClassName = Log\Driver\StringVar::className();
            $settings->storage = &$logString;
            return $settings;
        });

        OneABBR::migration()->rollback();
        $this->database->query(<<<SQL
CREATE TABLE `yaoi_tests_helper_entity_one_abbr` (
 `id` int NOT NULL AUTO_INCREMENT,
 `name` varchar(255) NOT NULL,
 `address` varchar(255),
 `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY `unique_name` (`name`),
 PRIMARY KEY (`id`)
)

SQL
)->execute();

        OneABBR::migration()->setLog($log)->apply();
        $this->assertSame(<<<LOG
Apply, table yaoi_tests_helper_entity_one_abbr (YaoiTests\Helper\Entity\OneABBR) requires migration
ALTER TABLE `yaoi_tests_helper_entity_one_abbr`
MODIFY COLUMN `name` varchar(255) NOT NULL DEFAULT ''
OK

LOG
            , $logString
);
    }
}
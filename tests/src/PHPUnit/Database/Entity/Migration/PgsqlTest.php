<?php

namespace YaoiTests\PHPUnit\Database\Entity\Migration;


use Yaoi\Database;
use Yaoi\Log;
use YaoiTests\Helper\Database\CheckAvailable;
use YaoiTests\Helper\Entity\Host;
use YaoiTests\Helper\Entity\Session;
use YaoiTests\Helper\Entity\User;

class PgsqlTest extends BaseTest
{

    public function setUp()
    {
        $this->database = CheckAvailable::getPgsql();
    }

    protected $expectedMigrationLog = <<<LOG
Table creation expected
Apply, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) requires migration
CREATE TABLE "yaoi_tests_helper_entity_user" (
 "id" SERIAL,
 "name" varchar(255) NOT NULL,
 PRIMARY KEY ("id")
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
ALTER TABLE "yaoi_tests_helper_entity_user"
ADD COLUMN "age" int,
ADD COLUMN "host_id" int NOT NULL,
ADD CONSTRAINT "k432f6fb01e8766435a432e5ed8ffb2ef" FOREIGN KEY ("host_id") REFERENCES "yaoi_tests_entity_host" ("id");
CREATE INDEX "key_age" ON "yaoi_tests_helper_entity_user" ("age");

OK
No action (up to date) expected
Apply, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) is up to date
Table revision increased, removed hostId, name, added sessionId, firstName, lastName
Apply, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) requires migration
Dependent migration required
Apply, table yaoi_tests_entity_session (YaoiTests\Helper\Entity\Session) is up to date
ALTER TABLE "yaoi_tests_helper_entity_user"
ADD COLUMN "session_id" int NOT NULL,
ADD COLUMN "first_name" varchar(255) NOT NULL,
ADD COLUMN "last_name" varchar(255) NOT NULL,
DROP COLUMN "name",
DROP COLUMN "host_id",
ADD CONSTRAINT "k42405537c0e04845e2902c8a7fb322be" FOREIGN KEY ("session_id") REFERENCES "yaoi_tests_entity_session" ("id"),
DROP CONSTRAINT IF EXISTS "k432f6fb01e8766435a432e5ed8ffb2ef";
CREATE UNIQUE INDEX "unique_last_name_first_name" ON "yaoi_tests_helper_entity_user" ("last_name", "first_name");

OK
No action (up to date) expected
Apply, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) is up to date
Table removal expected
Rollback, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) requires deletion
OK
No action (is already non-existent) expected
Rollback, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) is already non-existent

LOG;

    public function testUpdateSchema2()
    {
        $logString = '';
        $log = Log::getInstance(function () use (&$logString) {
            $settings = new Log\Settings();
            $settings->driverClassName = Log\Driver\StringVar::className();
            $settings->storage = &$logString;
            return $settings;
        });

        User::$revision = 2;
        User::bindDatabase($this->database, true);
        Host::bindDatabase($this->database, true);
        Session::bindDatabase($this->database, true);

        //$this->database->log(new Log('colored-stdout'));

        // prepare dependencies
        User::table()->migration()->rollback();
        Host::table()->migration()->apply();
        Session::table()->migration()->apply();

        Database\Entity\Migration::$enableStateCache = false;

        $log->push('Table revision increased, added age, hostId');
        User::$revision = 2;
        User::bindDatabase($this->database, true);
        User::table()->migration()->setLog($log)->apply();

        $log->push('No action (up to date) expected');
        User::bindDatabase($this->database, true);
        User::table()->migration()->setLog($log)->apply();

        $this->assertSame('Table revision increased, added age, hostId
Apply, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) requires migration
Dependent migration required
Apply, table yaoi_tests_entity_host (YaoiTests\Helper\Entity\Host) is up to date
CREATE TABLE "yaoi_tests_helper_entity_user" (
 "id" SERIAL,
 "name" varchar(255) NOT NULL,
 "age" int,
 "host_id" int NOT NULL,
 CONSTRAINT "k432f6fb01e8766435a432e5ed8ffb2ef" FOREIGN KEY ("host_id") REFERENCES "yaoi_tests_entity_host" ("id"),
 PRIMARY KEY ("id")
);
CREATE INDEX "key_age" ON "yaoi_tests_helper_entity_user" ("age");

OK
No action (up to date) expected
Apply, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) is up to date
', $logString);

    }


    public function testUpdateSchema3()
    {
        $logString = '';
        $log = Log::getInstance(function () use (&$logString) {
            $settings = new Log\Settings();
            $settings->driverClassName = Log\Driver\StringVar::className();
            $settings->storage = &$logString;
            return $settings;
        });

        User::$revision = 3;
        User::bindDatabase($this->database, true);
        Host::bindDatabase($this->database, true);
        Session::bindDatabase($this->database, true);

        //$this->database->log(new Log('colored-stdout'));

        // prepare dependencies
        User::table()->migration()->rollback();
        Host::table()->migration()->apply();
        Session::table()->migration()->apply();

        Database\Entity\Migration::$enableStateCache = false;

        $log->push('Table creation expected');
        User::$revision = 3;
        User::bindDatabase($this->database, true);
        User::table()->migration()->setLog($log)->apply();

        $log->push('No action (up to date) expected');
        User::bindDatabase($this->database, true);
        User::table()->migration()->setLog($log)->apply();

        $this->assertSame('Table creation expected
Apply, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) requires migration
Dependent migration required
Apply, table yaoi_tests_entity_session (YaoiTests\Helper\Entity\Session) is up to date
CREATE TABLE "yaoi_tests_helper_entity_user" (
 "id" SERIAL,
 "age" int,
 "session_id" int NOT NULL,
 "first_name" varchar(255) NOT NULL,
 "last_name" varchar(255) NOT NULL,
 CONSTRAINT "unique_last_name_first_name" UNIQUE ("last_name", "first_name"),
 CONSTRAINT "k42405537c0e04845e2902c8a7fb322be" FOREIGN KEY ("session_id") REFERENCES "yaoi_tests_entity_session" ("id"),
 PRIMARY KEY ("id")
);
CREATE INDEX "key_age" ON "yaoi_tests_helper_entity_user" ("age");

OK
No action (up to date) expected
Apply, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) is up to date
', $logString);

    }


}
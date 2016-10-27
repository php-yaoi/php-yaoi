<?php

namespace YaoiTests\PHPUnit\Database\Entity\Migration;


use Yaoi\Database;
use YaoiTests\Helper\Database\CheckAvailable;
use YaoiTests\PHPUnit\Database\Entity\Migration\BaseTest;

class SqliteTest extends BaseTest
{
    private $databaseFileName;

    public function setUp()
    {
        $this->databaseFileName = sys_get_temp_dir() . '/yaoi_test.sqlite';
        $this->database = new Database('sqlite:///' . $this->databaseFileName);
    }

    protected $expectedMigrationLog = <<<LOG
Table creation expected
# Apply, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) requires migration
CREATE TABLE `yaoi_tests_helper_entity_user` (
 `id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
 `name` varchar(255) NOT NULL
);
# OK
No action (up to date) expected
# Apply, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) is up to date
No action (up to date) expected
# Apply, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) is up to date
Table revision increased, added age, hostId
# Apply, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) requires migration
ALTER TABLE `yaoi_tests_helper_entity_user` RENAME TO _temp_table;
CREATE TABLE `yaoi_tests_helper_entity_user` (
 `id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
 `name` varchar(255) NOT NULL,
 `age` INTEGER DEFAULT NULL,
 `host_id` INTEGER NOT NULL
);
CREATE INDEX `yaoi_tests_helper_entity_user_key_age` ON `yaoi_tests_helper_entity_user` (`age`);
INSERT INTO `yaoi_tests_helper_entity_user` (`id`, `name`) SELECT `id`, `name` FROM _temp_table;
DROP TABLE _temp_table;
# Dependent tables found: yaoi_tests_entity_host
# Apply, table yaoi_tests_entity_host (YaoiTests\Helper\Entity\Host) is up to date
# OK
No action (up to date) expected
# Apply, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) is up to date
Table revision increased, removed hostId, name, added sessionId, firstName, lastName
# Apply, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) requires migration
DROP INDEX 'yaoi_tests_helper_entity_user_key_age';
ALTER TABLE `yaoi_tests_helper_entity_user` RENAME TO _temp_table;
CREATE TABLE `yaoi_tests_helper_entity_user` (
 `id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
 `age` INTEGER DEFAULT NULL,
 `session_id` INTEGER NOT NULL,
 `first_name` varchar(255) NOT NULL,
 `last_name` varchar(255) NOT NULL
);
CREATE INDEX `yaoi_tests_helper_entity_user_key_age` ON `yaoi_tests_helper_entity_user` (`age`);
CREATE UNIQUE INDEX `yaoi_tests_helper_entity_user_unique_last_name_first_name` ON `yaoi_tests_helper_entity_user` (`last_name`, `first_name`);
INSERT INTO `yaoi_tests_helper_entity_user` (`id`, `age`) SELECT `id`, `age` FROM _temp_table;
DROP TABLE _temp_table;
# Dependent tables found: yaoi_tests_entity_session
# Apply, table yaoi_tests_entity_session (YaoiTests\Helper\Entity\Session) is up to date
# OK
No action (up to date) expected
# Apply, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) is up to date
Table removal expected
# Rollback, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) requires deletion
DROP TABLE `yaoi_tests_helper_entity_user`;
# OK
No action (is already non-existent) expected
# Rollback, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) is already non-existent

LOG;


    public function tearDown()
    {
        $this->database = null;
        unlink($this->databaseFileName);
    }

}
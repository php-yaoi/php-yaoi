<?php

namespace YaoiTests\PHPUnit\Database\Entity;


use Yaoi\Database;
use YaoiTests\Helper\Database\CheckAvailable;

class SqliteComplexTest extends MysqlComplexTest
{
    private $databaseFileName;
    public function setUp() {
        $this->databaseFileName = sys_get_temp_dir() . '/yaoi_test.sqlite';
        $this->database = new Database('sqlite:///' . $this->databaseFileName);
    }

    protected $expectedMigrationLog = <<<LOG
    TODO fix broken
Apply, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) requires migration
CREATE TABLE `yaoi_tests_helper_entity_user` (
 `id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
 `name` varchar(255) NOT NULL
)
OK
Apply, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) is up to date
Apply, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) is up to date
Apply, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) requires migration
Dependent migration required
Apply, table yaoi_tests_entity_host (YaoiTests\Helper\Entity\Host) is up to date
ALTER TABLE `yaoi_tests_helper_entity_user` RENAME TO _temp_table;
CREATE TABLE `yaoi_tests_helper_entity_user` (
 `id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
 `name` varchar(255) NOT NULL,
 `age` INTEGER DEFAULT NULL,
 `host_id` INTEGER NOT NULL,
 CONSTRAINT `k432f6fb01e8766435a432e5ed8ffb2ef` FOREIGN KEY (`host_id`) REFERENCES `yaoi_tests_entity_host` (`id`)
);
CREATE INDEX `key_age` ON `yaoi_tests_helper_entity_user` (`age`);
INSERT INTO `yaoi_tests_helper_entity_user` (`id`, `name`) SELECT `id`, `name` FROM _temp_table;
DROP TABLE _temp_table;

OK
Apply, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) requires migration
Dependent migration required
Apply, table yaoi_tests_entity_host (YaoiTests\Helper\Entity\Host) is up to date
ALTER TABLE `yaoi_tests_helper_entity_user` RENAME TO _temp_table;
CREATE TABLE `yaoi_tests_helper_entity_user` (
 `id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
 `name` varchar(255) NOT NULL,
 `age` INTEGER DEFAULT NULL,
 `host_id` INTEGER NOT NULL,
 CONSTRAINT `k432f6fb01e8766435a432e5ed8ffb2ef` FOREIGN KEY (`host_id`) REFERENCES `yaoi_tests_entity_host` (`id`)
);
CREATE INDEX `key_age` ON `yaoi_tests_helper_entity_user` (`age`);
INSERT INTO `yaoi_tests_helper_entity_user` (`id`, `name`, `age`, `host_id`) SELECT `id`, `name`, `age`, `host_id` FROM _temp_table;
DROP TABLE _temp_table;

1 index key_age already exists
Apply, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) requires migration
Dependent migration required
Apply, table yaoi_tests_entity_session (YaoiTests\Helper\Entity\Session) is up to date
ALTER TABLE `yaoi_tests_helper_entity_user` RENAME TO _temp_table;
CREATE TABLE `yaoi_tests_helper_entity_user` (
 `id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
 `age` INTEGER DEFAULT NULL,
 `session_id` INTEGER NOT NULL,
 `first_name` varchar(255) NOT NULL,
 `last_name` varchar(255) NOT NULL,
 CONSTRAINT `k42405537c0e04845e2902c8a7fb322be` FOREIGN KEY (`session_id`) REFERENCES `yaoi_tests_entity_session` (`id`)
);
CREATE INDEX `key_age` ON `yaoi_tests_helper_entity_user` (`age`);
CREATE UNIQUE INDEX `unique_last_name_first_name` ON `yaoi_tests_helper_entity_user` (`last_name`, `first_name`);
INSERT INTO `yaoi_tests_helper_entity_user` (`id`, `age`) SELECT `id`, `age` FROM _temp_table;
DROP TABLE _temp_table;

1 table `yaoi_tests_helper_entity_user` already exists
Apply, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) requires migration
Dependent migration required
Apply, table yaoi_tests_entity_session (YaoiTests\Helper\Entity\Session) is up to date
ALTER TABLE `yaoi_tests_helper_entity_user` RENAME TO _temp_table;
CREATE TABLE `yaoi_tests_helper_entity_user` (
 `id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
 `age` INTEGER DEFAULT NULL,
 `session_id` INTEGER NOT NULL,
 `first_name` varchar(255) NOT NULL,
 `last_name` varchar(255) NOT NULL,
 CONSTRAINT `k42405537c0e04845e2902c8a7fb322be` FOREIGN KEY (`session_id`) REFERENCES `yaoi_tests_entity_session` (`id`)
);
CREATE INDEX `key_age` ON `yaoi_tests_helper_entity_user` (`age`);
CREATE UNIQUE INDEX `unique_last_name_first_name` ON `yaoi_tests_helper_entity_user` (`last_name`, `first_name`);
INSERT INTO `yaoi_tests_helper_entity_user` (`id`, `age`) SELECT `id`, `age` FROM _temp_table;
DROP TABLE _temp_table;

1 table `yaoi_tests_helper_entity_user` already exists
Rollback, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) requires deletion
OK

LOG;


    public function tearDown()
    {
        $this->database = null;
        unlink($this->databaseFileName);
    }

}
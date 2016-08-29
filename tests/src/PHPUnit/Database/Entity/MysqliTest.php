<?php

namespace YaoiTests\PHPUnit\Database\Entity;

class MysqliTest extends \YaoiTests\PHPUnit\Database\Entity\TestBase
{

    protected $entityOneCreateTableExpected = <<<SQL
CREATE TABLE `yaoi_tests_helper_entity_one_abbr` (
 `id` int NOT NULL AUTO_INCREMENT,
 `name` varchar(255) NOT NULL DEFAULT '',
 `address` varchar(255) DEFAULT NULL,
 `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY `unique_name` (`name`),
 PRIMARY KEY (`id`)
)
SQL;

    protected $entityTwoCreateTableExpected = "CREATE TABLE `custom_name` (
 `id` int NOT NULL AUTO_INCREMENT,
 `one_id` int NOT NULL DEFAULT '0',
 `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `info` varchar(255) DEFAULT NULL,
 KEY `key_info` (`info`),
 CONSTRAINT `fk_custom_name_one_id_yaoi_tests_helper_entity_one_abbr_id` FOREIGN KEY (`one_id`) REFERENCES `yaoi_tests_helper_entity_one_abbr` (`id`),
 PRIMARY KEY (`id`)
)";

    public function setUp() {
        $this->database = \YaoiTests\Helper\Database\CheckAvailable::getMysqli();
        parent::setUp();
    }

    protected $expectedMigrateLog = <<<EOD
Rollback, table yaoi_tests_entity_session (YaoiTests\Helper\Entity\Session) is already non-existent
Rollback, table yaoi_tests_entity_host (YaoiTests\Helper\Entity\Host) is already non-existent
Rollback, table yaoi_tests_entity_tag (YaoiTests\Helper\Entity\Tag) is already non-existent
Rollback, table yaoi_tests_entity_session_tag (YaoiTests\Helper\Entity\SessionTag) is already non-existent
Apply, table yaoi_tests_entity_session (YaoiTests\Helper\Entity\Session) requires migration
Dependent migration required
Apply, table yaoi_tests_entity_host (YaoiTests\Helper\Entity\Host) requires migration
CREATE TABLE `yaoi_tests_entity_host` (
 `id` int NOT NULL AUTO_INCREMENT,
 `name` varchar(255) NOT NULL DEFAULT '',
 UNIQUE KEY `unique_name` (`name`),
 PRIMARY KEY (`id`)
)
OK
CREATE TABLE `yaoi_tests_entity_session` (
 `id` int NOT NULL AUTO_INCREMENT,
 `host_id` int NOT NULL DEFAULT '0',
 `started_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `ended_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT `fk_yaoi_tests_entity_session_host_id_yaoi_tests_entity_host_id` FOREIGN KEY (`host_id`) REFERENCES `yaoi_tests_entity_host` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
 PRIMARY KEY (`id`)
)
OK
Apply, table yaoi_tests_entity_host (YaoiTests\Helper\Entity\Host) is up to date
Apply, table yaoi_tests_entity_tag (YaoiTests\Helper\Entity\Tag) requires migration
CREATE TABLE `yaoi_tests_entity_tag` (
 `id` int NOT NULL AUTO_INCREMENT,
 `name` varchar(255) NOT NULL DEFAULT '',
 UNIQUE KEY `unique_name` (`name`),
 PRIMARY KEY (`id`)
)
OK
Apply, table yaoi_tests_entity_session_tag (YaoiTests\Helper\Entity\SessionTag) requires migration
Dependent migration required
Apply, table yaoi_tests_entity_session (YaoiTests\Helper\Entity\Session) is up to date
Dependent migration required
Apply, table yaoi_tests_entity_tag (YaoiTests\Helper\Entity\Tag) is up to date
CREATE TABLE `yaoi_tests_entity_session_tag` (
 `session_id` int NOT NULL DEFAULT '0',
 `tag_id` int NOT NULL DEFAULT '0',
 `added_at_ut` int NOT NULL DEFAULT '0',
 PRIMARY KEY (`session_id`, `tag_id`)
)
OK
Apply, table yaoi_tests_entity_session (YaoiTests\Helper\Entity\Session) is up to date
Apply, table yaoi_tests_entity_host (YaoiTests\Helper\Entity\Host) is up to date
Apply, table yaoi_tests_entity_tag (YaoiTests\Helper\Entity\Tag) is up to date
Apply, table yaoi_tests_entity_session_tag (YaoiTests\Helper\Entity\SessionTag) is up to date
Rollback, table yaoi_tests_entity_session (YaoiTests\Helper\Entity\Session) requires deletion
Dependent migration required
Rollback, table yaoi_tests_entity_session_tag (YaoiTests\Helper\Entity\SessionTag) requires deletion
OK
OK
Rollback, table yaoi_tests_entity_host (YaoiTests\Helper\Entity\Host) requires deletion
Dependent migration required
Rollback, table yaoi_tests_entity_session (YaoiTests\Helper\Entity\Session) is already non-existent
OK
Rollback, table yaoi_tests_entity_tag (YaoiTests\Helper\Entity\Tag) requires deletion
Dependent migration required
Rollback, table yaoi_tests_entity_session_tag (YaoiTests\Helper\Entity\SessionTag) is already non-existent
OK
Rollback, table yaoi_tests_entity_session_tag (YaoiTests\Helper\Entity\SessionTag) is already non-existent
Rollback, table yaoi_tests_entity_session (YaoiTests\Helper\Entity\Session) is already non-existent
Rollback, table yaoi_tests_entity_host (YaoiTests\Helper\Entity\Host) is already non-existent
Rollback, table yaoi_tests_entity_tag (YaoiTests\Helper\Entity\Tag) is already non-existent
Rollback, table yaoi_tests_entity_session_tag (YaoiTests\Helper\Entity\SessionTag) is already non-existent

EOD;


}
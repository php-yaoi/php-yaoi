<?php

namespace YaoiTests\PHPUnit\Database\Entity\Migration;


use Yaoi\Log;
use YaoiTests\Helper\Entity\OneABBR;

class MysqlTest extends BaseTest
{
    protected $expectedMigrationLog = <<<EOD
Table creation expected
# Apply, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) requires migration
CREATE TABLE `yaoi_tests_helper_entity_user` (
 `id` int NOT NULL AUTO_INCREMENT,
 `name` varchar(255) NOT NULL DEFAULT '',
 PRIMARY KEY (`id`)
);
# OK
No action (up to date) expected
# Apply, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) is up to date
No action (up to date) expected
# Apply, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) is up to date
Table revision increased, added age, hostId
# Apply, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) requires migration
ALTER TABLE `yaoi_tests_helper_entity_user`
ADD COLUMN `age` int DEFAULT NULL,
ADD COLUMN `host_id` int NOT NULL DEFAULT '0',
ADD INDEX `key_age` (`age`);
# Dependent tables found: yaoi_tests_entity_host
# Apply, table yaoi_tests_entity_host (YaoiTests\Helper\Entity\Host) is up to date
ALTER TABLE `yaoi_tests_helper_entity_user`
ADD CONSTRAINT `k432f6fb01e8766435a432e5ed8ffb2ef` FOREIGN KEY (`host_id`) REFERENCES `yaoi_tests_entity_host` (`id`);
# OK
No action (up to date) expected
# Apply, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) is up to date
Table revision increased, removed hostId, name, added sessionId, firstName, lastName
# Apply, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) requires migration
ALTER TABLE `yaoi_tests_helper_entity_user`
ADD COLUMN `session_id` int NOT NULL DEFAULT '0',
ADD COLUMN `first_name` varchar(255) NOT NULL DEFAULT '',
ADD COLUMN `last_name` varchar(255) NOT NULL DEFAULT '',
DROP COLUMN `name`,
DROP COLUMN `host_id`,
ADD UNIQUE INDEX `unique_last_name_first_name` (`last_name`, `first_name`),
DROP INDEX `k432f6fb01e8766435a432e5ed8ffb2ef`,
DROP FOREIGN KEY `k432f6fb01e8766435a432e5ed8ffb2ef`;
# Dependent tables found: yaoi_tests_entity_session
# Apply, table yaoi_tests_entity_session (YaoiTests\Helper\Entity\Session) is up to date
ALTER TABLE `yaoi_tests_helper_entity_user`
ADD CONSTRAINT `k42405537c0e04845e2902c8a7fb322be` FOREIGN KEY (`session_id`) REFERENCES `yaoi_tests_entity_session` (`id`);
# OK
No action (up to date) expected
# Apply, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) is up to date
Table removal expected
# Rollback, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) requires deletion
DROP TABLE `yaoi_tests_helper_entity_user`;
# OK
No action (is already non-existent) expected
# Rollback, table yaoi_tests_helper_entity_user (YaoiTests\Helper\Entity\User) is already non-existent

EOD;


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
# Apply, table yaoi_tests_helper_entity_one_abbr (YaoiTests\Helper\Entity\OneABBR) is up to date

LOG
            , $logString
        );
    }

}
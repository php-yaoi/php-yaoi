<?php

class DatabaseEntityMysqliTest extends \YaoiTests\DatabaseEntity\TestCase
{

    protected $entityOneCreateTableExpected = "CREATE TABLE `yaoi_tests_entity_one_abbr` (
 `id` int AUTO_INCREMENT,
 `name` varchar(255) NOT NULL,
 `address` varchar(255) DEFAULT '',
 `created_at` timestamp DEFAULT '0',
 UNIQUE KEY (`name`),
 PRIMARY KEY (`id`)
)
";
    protected $entityTwoCreateTableExpected = "CREATE TABLE `custom_name` (
 `id` int AUTO_INCREMENT,
 `one_id` int,
 `created_at` timestamp DEFAULT '0',
 `updated_at` timestamp DEFAULT '0',
 `info` varchar(255),
 KEY (`info`),
 CONSTRAINT `custom_name_one_id` FOREIGN KEY (`one_id`) REFERENCES `yaoi_tests_entity_one_abbr` (`id`),
 PRIMARY KEY (`id`)
)
";

    public function setUp() {
        try {
            $this->database = \Yaoi\Database::getInstance('test_mysqli');
        }
        catch (\Yaoi\Service\Exception $exception) {
            $this->markTestSkipped($exception->getMessage());
        }

    }

}
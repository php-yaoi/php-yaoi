<?php
namespace YaoiTests\PHPUnit\Entity;

use Yaoi\Database;
use Yaoi\Test\PHPUnit\TestCase;

/**
 * Class Entity_SimpleMysqlTest
 * @deprecated
 */
class SimpleMysqlTest extends TestCase
{

    public function setUp()
    {
        try {
            Database::getInstance('test_mysqli');
        } catch (\Yaoi\Service\Exception $exception) {
            $this->markTestSkipped($exception->getMessage());
        }

    }

    public function testMain()
    {
        $createTable = <<<SQL
CREATE TABLE IF NOT EXISTS `AutoTabled` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `banner_set_id` int(11) DEFAULT NULL,
  `banner_format_id` int(11) DEFAULT NULL,
  `banner_file` varchar(255) DEFAULT NULL,
  `alt_banner_file` varchar(255) NOT NULL DEFAULT '',
  `file_type` tinyint(4) NOT NULL,
  `created` int(11) NOT NULL DEFAULT 0,
  `url` varchar(255) DEFAULT NULL,
  `interactive` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `type` enum('image','html') NOT NULL DEFAULT 'image',
  `html` text, PRIMARY KEY (`id`),
  KEY `banner_set_id` (`banner_set_id`)
  )
  ENGINE=InnoDB AUTO_INCREMENT=1613 DEFAULT CHARSET=utf8
SQL;

        $db = Database::getInstance('test_mysqli', false);
        //$db->log(Log::getInstance('stdout://'));
        \YaoiTests\Helper\Entity\AutoTabled::bindDatabase($db);


        $db->query("DROP TABLE IF EXISTS `AutoTabled`");
        $db->query($createTable);

        $itemA = new \YaoiTests\Helper\Entity\AutoTabled();
        $itemA->banner_file = '123';
        $itemA->file_type = '2';
        $itemA->save();

        $itemA->banner_file = '321';
        $itemA->html = 'ololo';
        $itemA->save();


        $itemB = \YaoiTests\Helper\Entity\AutoTabled::getById($itemA->id);
        $this->assertSame($itemA->banner_file, $itemB->banner_file);

        $itemA->created = '100001';
        $itemA->save();

        $itemC = \YaoiTests\Helper\Entity\AutoTabled::getById($itemA->id);
        $this->assertSame($itemA->created, $itemC->created);

        $db->query("DROP TABLE `AutoTabled`");
    }
}


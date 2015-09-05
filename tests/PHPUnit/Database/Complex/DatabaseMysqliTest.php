<?php

use Yaoi\Database;
use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Table;
use Yaoi\Database\Definition\Index;

require_once __DIR__ . '/DatabaseTestUnified.php';
class DatabaseMysqliTest extends \YaoiTests\DatabaseTestUnified {
    public function setUp() {
        \YaoiTests\Database\CheckAvailable::checkMysqli();
        $this->db = Database::getInstance('test_mysqli');
    }


    protected $createTableStatement = "CREATE TABLE `test_indexes` (
 `id` int NOT NULL AUTO_INCREMENT,
 `name` varchar(255) NOT NULL,
 `uni_one` int DEFAULT NULL,
 `uni_two` int DEFAULT NULL,
 `default_null` float DEFAULT NULL,
 `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
 `ref_id` int NOT NULL,
 `r_one` varchar(255) DEFAULT NULL,
 `r_two` varchar(255) DEFAULT NULL,
 UNIQUE KEY `unique_uni_one_uni_two` (`uni_one`, `uni_two`),
 KEY `key_name` (`name`),
 CONSTRAINT `fk_test_indexes_ref_id_table_a_id` FOREIGN KEY (`ref_id`) REFERENCES `table_a` (`id`),
 CONSTRAINT `fk_test_indexes_r_one_r_two_table_a_m_one_table_a_m_two` FOREIGN KEY (`r_one`, `r_two`) REFERENCES `table_a` (`m_one`, `m_two`),
 PRIMARY KEY (`id`)
)";


    /**
     * @throws Database\Exception
     * @see \Yaoi\Database\Entity
     */
    public function testUtilityTypeString() {
        /** @var Database\Utility\Mysql $utility */
        $utility = $this->db->getUtility();

        $this->assertSame(
            'int unsigned NOT NULL DEFAULT 15',
            $utility->getColumnTypeString(
                Column::create(Column::INTEGER | Column::UNSIGNED | Column::NOT_NULL)->setDefault(15)
            )
        );

        $this->assertSame(
            'float DEFAULT NULL',
            $utility->getColumnTypeString(
                Column::create(Column::FLOAT)
            )
        );

        $this->assertSame(
            'varchar(255) NOT NULL DEFAULT \'default\'',
            $utility->getColumnTypeString(
                Column::create(Column::STRING | Column::NOT_NULL)
                    ->setDefault('default')
            )
        );

        $this->assertSame(
            'timestamp DEFAULT NULL', // TODO utility check columns!
            $utility->getColumnTypeString(
                Column::create(Column::TIMESTAMP)
            )
        );


        $this->assertSame(
            'char(12) NOT NULL DEFAULT \'default\'',
            $utility->getColumnTypeString(
                Column::create(Column::STRING | Column::NOT_NULL)
                    ->setDefault('default')
                    ->setStringLength(12, true)
            )
        );
    }


    public function testUtilityCreateTable() {
        /** @var Database\Utility\Mysql $utility */
        $utility = $this->db->getUtility();


        $columns2 = new \stdClass();
        $columns2->id = Column::create(Column::INTEGER + Column::AUTO_ID + Column::NOT_NULL + Column::UNSIGNED);
        $columns2->meta = Column::create(Column::STRING);
        $table2 = Table::create($columns2, $this->db, 'test2');


        $columns = new \stdClass();
        $columns->id = Column::create(Column::INTEGER + Column::AUTO_ID + Column::NOT_NULL + Column::UNSIGNED);
        $columns->fk_id = Column::create(Column::INTEGER + Column::NOT_NULL + Column::UNSIGNED)
            ->setForeignKey($table2->getColumns()->id);

        $columns->fk_id2 = Column::create(Column::INTEGER + Column::NOT_NULL + Column::UNSIGNED);
        $columns->dateUt = Column::create(Column::TIMESTAMP)->setDefault(null);
        $columns->name = Column::create(Column::STRING + Column::NOT_NULL)->setDefault('');
        $columns->seconds = Column::create(Column::FLOAT + Column::NOT_NULL)->setDefault(0);
        $columns->type = Column::create(Column::STRING)->setStringLength(10, true);

        $table = Table::create($columns, $this->db, 'test_entity')
            ->setPrimaryKey($columns->id)
            ->addIndex(Index::TYPE_UNIQUE, $columns->dateUt, $columns->name, $columns->type);

        $sql = $table->getCreateTable();

        $this->assertStringEqualsCRLF("CREATE TABLE `test_entity` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `fk_id` int unsigned NOT NULL,
 `fk_id2` int unsigned NOT NULL,
 `date_ut` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
 `name` varchar(255) NOT NULL DEFAULT '',
 `seconds` float NOT NULL DEFAULT 0,
 `type` char(10) DEFAULT NULL,
 UNIQUE KEY `unique_date_ut_name_type` (`date_ut`, `name`, `type`),
 CONSTRAINT `fk_test_entity_fk_id_test2_id` FOREIGN KEY (`fk_id`) REFERENCES `test2` (`id`),
 PRIMARY KEY (`id`)
)", (string)$sql);
    }

    public function testUtilityCreateTable2() {
        /** @var Database\Utility\Mysql $utility */
        $utility = $this->db->getUtility();

        $columns = new \stdClass();
        $columns->id = Column::create(Column::INTEGER + Column::AUTO_ID + Column::NOT_NULL + Column::UNSIGNED);
        $columns->branch = Column::create(Column::STRING + Column::NOT_NULL);
        $columns->duration = Column::create(Column::FLOAT + Column::NOT_NULL);
        $columns->entity = Column::create(Column::STRING);
        $columns->language = Column::create(Column::STRING);
        $columns->project = Column::create(Column::STRING);
        $columns->time = Column::create(Column::INTEGER);
        $columns->type = Column::create(Column::STRING);

        $table = Table::create($columns, $this->db, 'test_name')->setPrimaryKey($columns->id);

        $sql = $utility->generateCreateTableOnDefinition($table);

        $this->assertStringEqualsCRLF('CREATE TABLE `test_name` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `branch` varchar(255) NOT NULL,
 `duration` float NOT NULL,
 `entity` varchar(255) DEFAULT NULL,
 `language` varchar(255) DEFAULT NULL,
 `project` varchar(255) DEFAULT NULL,
 `time` int DEFAULT NULL,
 `type` varchar(255) DEFAULT NULL,
 PRIMARY KEY (`id`)
)', (string)$sql);

    }



    protected $testCreateIndexesAlterExpected = <<<SQL
ALTER TABLE `test_indexes`
ADD COLUMN `new_field` char(15) NOT NULL DEFAULT 'normal',
ADD UNIQUE INDEX `unique_updated` (`updated`),
DROP INDEX `unique_uni_one_uni_two`,
DROP INDEX `key_name`
SQL;

    protected $testCreateTableAfterAlter = <<<SQL
CREATE TABLE `test_indexes` (
 `id` int NOT NULL AUTO_INCREMENT,
 `name` varchar(255) NOT NULL,
 `uni_one` int DEFAULT NULL,
 `uni_two` int DEFAULT NULL,
 `default_null` float DEFAULT NULL,
 `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
 `new_field` varchar(255) NOT NULL DEFAULT 'normal',
 UNIQUE KEY `unique_updated` (`updated`),
 PRIMARY KEY (`id`)
)
SQL;



}
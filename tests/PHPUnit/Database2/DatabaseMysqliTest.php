<?php

use Yaoi\Database;
use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Table;
use Yaoi\Database\Definition\Index;

require_once __DIR__ . '/DatabaseTestUnified.php';
class DatabaseMysqliTest extends DatabaseTestUnified {
    public function setUp() {
        try {
            $this->db = Database::getInstance('test_mysqli');
        }
        catch (\Yaoi\Service\Exception $exception) {
            $this->markTestSkipped($exception->getMessage());
        }

    }


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
            'float',
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
            'timestamp DEFAULT \'0\'',
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
        $table2 = Table::create($columns2);
        $table2->schemaName = 'test2';


        $columns = new \stdClass();
        $columns->id = Column::create(Column::INTEGER + Column::AUTO_ID + Column::NOT_NULL + Column::UNSIGNED);
        $columns->fk_id = Column::create(Column::INTEGER + Column::NOT_NULL + Column::UNSIGNED)
            ->setConstraint($table2->getColumns()->id);

        $columns->fk_id2 = Column::create(Column::INTEGER + Column::NOT_NULL + Column::UNSIGNED);
        $columns->dateUt = Column::create(Column::TIMESTAMP)->setDefault(null);
        $columns->name = Column::create(Column::STRING + Column::NOT_NULL)->setDefault('');
        $columns->seconds = Column::create(Column::FLOAT + Column::NOT_NULL)->setDefault(0);
        $columns->type = Column::create(Column::STRING)->setStringLength(10, true);

        $table = Table::create($columns)
            ->setSchemaName('test_entity')
            ->setPrimaryKey($columns->id)
            ->addIndex(Index::TYPE_UNIQUE, $columns->dateUt, $columns->name, $columns->type);

        $sql = $utility->generateCreateTableOnDefinition($table);

        $this->assertSame('CREATE TABLE `test_entity` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `fk_id` int unsigned NOT NULL,
 `fk_id2` int unsigned NOT NULL,
 `date_ut` timestamp DEFAULT NULL,
 `name` varchar(255) NOT NULL DEFAULT \'\',
 `seconds` float NOT NULL DEFAULT 0,
 `type` char(10),
 UNIQUE KEY (`date_ut`, `name`, `type`),
 CONSTRAINT `test_entity_fk_id` FOREIGN KEY (`fk_id`) REFERENCES `test2` (`id`),
 PRIMARY KEY (`id`)
)
', $sql);
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

        $table = Table::create($columns)->setSchemaName('test_name')->setPrimaryKey($columns->id);

        $sql = $utility->generateCreateTableOnDefinition($table);

        $this->assertSame('CREATE TABLE `test_name` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `branch` varchar(255) NOT NULL,
 `duration` float NOT NULL,
 `entity` varchar(255),
 `language` varchar(255),
 `project` varchar(255),
 `time` int,
 `type` varchar(255),
 PRIMARY KEY (`id`)
)
', $sql);

    }

}
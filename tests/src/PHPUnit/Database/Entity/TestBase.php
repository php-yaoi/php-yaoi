<?php
namespace YaoiTests\PHPUnit\Database\Entity;

use Yaoi\Database\Definition\Column;
use Yaoi\Database\Definition\Table;
use Yaoi\Database\Exception;
use Yaoi\Log;
use Yaoi\Migration\Manager;
use Yaoi\Migration\Migration;
use Yaoi\Undefined;
use YaoiTests\Helper\Entity\Host;
use YaoiTests\Helper\Entity\Session;
use YaoiTests\Helper\Entity\SessionTag;
use YaoiTests\Helper\Entity\Tag;
use YaoiTests\Helper\Entity\OneABBR;
use YaoiTests\Helper\Entity\TestEmptyInsert;
use YaoiTests\Helper\Entity\Two;
use YaoiTests\Helper\Entity\User;

abstract class TestBase extends \Yaoi\Test\PHPUnit\TestCase
{
    /** @var  \Yaoi\Database */
    protected $database;

    protected $expectedMigrateLog;

    /**
     * By default column has STRING type
     * @see Column::__construct
     */
    public function testDefaultColumn() {
        $this->assertSame(Column::STRING, Column::create()->flags);
    }


    /**
     * AUTO_ID column is INTEGER by default
     * @see Column::__construct
     */
    public function testAutoIdColumn() {
        $this->assertSame(Column::AUTO_ID + Column::INTEGER + Column::NOT_NULL, Column::create(Column::AUTO_ID)->flags);
        $this->assertSame(Column::AUTO_ID + Column::INTEGER,
            Column::create(Column::AUTO_ID + Column::INTEGER)->flags);
    }

    /**
     * AUTO_ID column is primary key
     * @see Column::__construct
     * @todo throw exception on multiple AUTO_ID and setting custom PK when AUTO_ID is set
     */
    public function testAutoIdPrimary() {
        $columns = new \stdClass();
        $columns->id = new Column(Column::AUTO_ID);

        $table = new Table($columns, $this->database, 'some_table');
        $this->assertSame(array('id' => $columns->id), $table->primaryKey);
    }


    public function testColumns() {
        $table = OneABBR::table();
        $columnsFlags = array();
        foreach ($table->getColumns(true) as $column) {
            $columnsFlags[$column->propertyName] = $column->flags;
        }

        $this->assertArrayBitwiseAnd(array(
            'id' => Column::AUTO_ID + Column::INTEGER,
            'name' => Column::STRING + Column::NOT_NULL,
            'address' => Column::STRING,
            'createdAt' => Column::TIMESTAMP,
        ), $columnsFlags);
    }


    /**
     * @see \Yaoi\Database\Definition\Table::schemaName
     */
    public function testSchemaName() {
        $this->assertSame('yaoi_tests_helper_entity_one_abbr', OneABBR::table()->schemaName);
        $this->assertSame('custom_name', Two::table()->schemaName);
    }

    /**
     * @see Yaoi\Database\Definition\Table::schemaName
     */
    public function testClassName() {
        $this->assertSame('YaoiTests\Helper\Entity\OneABBR', OneABBR::table()->entityClassName);
        $this->assertSame('YaoiTests\Helper\Entity\Two', Two::table()->entityClassName);
    }


    protected $entityOneCreateTableExpected;
    protected $entityTwoCreateTableExpected;


    public function testCreateTable() {
        $this->assertStringEqualsCRLF(
            $this->entityOneCreateTableExpected,
            (string)$this->database->getUtility()
                ->generateCreateTableOnDefinition(OneABBR::table()));

        $this->assertStringEqualsCRLF(
            $this->entityTwoCreateTableExpected,
            (string)$this->database->getUtility()
                ->generateCreateTableOnDefinition(Two::table()));

    }




    public function testMigrate() {
        /** @var Table[] $tables */
        $tables = array(
            Session::table(),
            Host::table(),
            Tag::table(),
            SessionTag::table(),
        );

        $log = new Log('stdout');

        $remover = new Manager();
        foreach ($tables as $table) {
            $remover->add($table->migration(), Migration::ROLLBACK);
        }
        $remover->run();
        $remover->setLog($log);


        $adder = new Manager();
        $adder->setLog($log);
        foreach ($tables as $table) {
            $adder->add($table->migration());
        }


        ob_start();
        $remover->run();
        $adder->run();
        $adder->run();
        $remover->run();
        $remover->run();
        $logData = ob_get_clean();

        ob_start();
        $adder->run();
        ob_end_clean();

        $this->assertStringEqualsCRLF($this->expectedMigrateLog, $logData);
    }


    protected $expectedBindsStatement = <<<SQL
SELECT `yaoi_tests_entity_tag`.`id`, `yaoi_tests_entity_tag`.`name`
FROM `yaoi_tests_entity_host`
LEFT JOIN `yaoi_tests_entity_session` ON `yaoi_tests_entity_session`.`host_id` = `yaoi_tests_entity_host`.`id`
LEFT JOIN `yaoi_tests_entity_session_tag` ON `yaoi_tests_entity_session`.`id` = `yaoi_tests_entity_session_tag`.`session_id`
LEFT JOIN `yaoi_tests_entity_tag` ON `yaoi_tests_entity_tag`.`id` = `yaoi_tests_entity_session_tag`.`tag_id`
WHERE `yaoi_tests_entity_host`.`id` = 12
GROUP BY `yaoi_tests_entity_tag`.`id`
SQL;

    public function testBinds() {
        $select = Host::statement()
            ->select(Tag::columns())
            ->where('? = ?', Host::columns()->id, 12)
            ->leftJoin('? ON ? = ?', Session::table(), Session::columns()->hostId, Host::columns()->id)
            ->leftJoin('? ON ? = ?', SessionTag::table(), Session::columns()->id, SessionTag::columns()->sessionId)
            ->leftJoin('? ON ? = ?', Tag::table(), Tag::columns()->id, SessionTag::columns()->tagId)
            ->groupBy(Tag::columns()->id)
            ->bindResultClass(Tag::className());

        $this->assertStringEqualsSpaceless($this->expectedBindsStatement, $select->build());
    }


    /**
     * @see Entity::save
     * @see Entity::delete
     * @see Entity::findByPrimaryKey
     * @throws \Yaoi\Entity\Exception
     */
    public function testLifeCycle() {
        $host = new Host();
        $host->name = 'test';
        $host->save();
        $this->assertSame(1, $host->id);

        $host->name = 'Test';
        $host->save();

        $host2 = new Host();
        $host2->name = 'Test 2';
        $host2->save();
        $this->assertSame(2, $host2->id);

        $this->assertSame('Test', Host::findByPrimaryKey($host->id)->name);
        $host->delete();

        $this->assertNull(Host::findByPrimaryKey($host->id));

    }

    public function testSaveWithModKey()
    {
        $host = new Host();
        $host->name = 'some';
        if ($existing = $host->findSaved()) {
            $existing->delete();
        }

        $host->save();

        $host->id = 0;
        $host->save();

        $this->assertSame('some', Host::findByPrimaryKey(0)->name);

        $host->delete();
        $this->assertSame(null, Host::findByPrimaryKey(0));

    }


    public function testFindOrSave() {
        $sessionTag = new SessionTag();
        $sessionTag->sessionId = 123;
        $sessionTag->tagId = 456;
        try {
            SessionTag::statement($sessionTag)->delete()->query();
        }
        catch (Exception $exception) {
            echo $exception->query;
        }

        $sessionTag = new SessionTag();
        $sessionTag->sessionId = 123;
        $sessionTag->tagId = 456;
        $sessionTag->addedAtUt = 123123;

        $sessionTag->findOrSave();

        $sessionTag = new SessionTag();
        $sessionTag->sessionId = 123;
        $sessionTag->tagId = 456;
        $sessionTag->addedAtUt = 456456;

        $sessionTag->findOrSave();

        $this->assertSame(123123, $sessionTag->addedAtUt);
    }

    public function testEmptyInsert() {
        $item = new TestEmptyInsert();
        $item->save();
        $this->assertSame(1, $item->id);
    }

    /**
     * @expectedException \Yaoi\Entity\Exception
     * @expectedExceptionCode \Yaoi\Entity\Exception::KEY_MISSING
     * @throws \Yaoi\Entity\Exception
     */
    public function testFindFullPrimaryRequired() {
        SessionTag::findByPrimaryKey(123);
    }
    /**
     * @expectedException \Yaoi\Entity\Exception
     * @expectedExceptionCode \Yaoi\Entity\Exception::KEY_MISSING
     * @throws \Yaoi\Entity\Exception
     */
    public function testUpdateFullPrimaryRequired() {
        $sessionTag = new SessionTag();
        $sessionTag->sessionId = 123;
        $sessionTag->addedAtUt = time();
        $sessionTag->update();
    }

    /**
     * @expectedException \Yaoi\Entity\Exception
     * @expectedExceptionCode \Yaoi\Entity\Exception::KEY_MISSING
     * @throws \Yaoi\Entity\Exception
     */
    public function testDeleteFullPrimaryRequired() {
        $sessionTag = new SessionTag();
        $sessionTag->sessionId = 123;
        $sessionTag->delete();
    }


    public function setUp() {
        Host::bindDatabase($this->database);
        Session::bindDatabase($this->database);
        SessionTag::bindDatabase($this->database);
        Tag::bindDatabase($this->database);

        TestEmptyInsert::bindDatabase($this->database);
        TestEmptyInsert::table()->migration()->apply();

    }

    public function testFindSaved() {
        User::bindDatabase($this->database, true);
        User::table()->migration()->apply();

        $user = new User();
        $user->name = 123;
        $this->assertSame(Undefined::get(), $user->id);

        $user->findOrSave();
        $this->assertInternalType('int', $user->id);
    }


    public function tearDown()
    {
        TestEmptyInsert::table()->migration()->rollback();
    }


}
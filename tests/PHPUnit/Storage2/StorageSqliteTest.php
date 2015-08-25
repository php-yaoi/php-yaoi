<?php

use Yaoi\Database;
use Yaoi\Migration\Manager;
use Yaoi\Migration\Needed;
use Yaoi\Storage;
use Yaoi\Storage\Driver\DatabaseProxy;
use Yaoi\Storage\Driver\JsonProxy;
use Yaoi\Storage\Settings;

require_once __DIR__ . '/StorageMysqlTest.php';

class StorageSqliteTest extends \StorageMysqlTest {

    private $sqliteFilename;
    public function setUp() {
        if (!class_exists('SQLite3')) {
            $this->markTestSkipped('SQLite extension not available');
        }

        $this->sqliteFilename =  sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test-sqlite.sqlite';
        echo $this->sqliteFilename;
        $this->db = new Database('sqlite:///' . $this->sqliteFilename);

        //$db->log(new Log('stdout'));

        $dsn = new Settings();
        $dsn->proxyClient = $this->db;
        //$dsn->path = 'storage';
        $dsn->driverClassName = DatabaseProxy::className();

        $this->storage = new Storage($dsn);
        $driver = $this->storage->getDriver();
        if ($driver instanceof Needed) {
            Manager::getInstance()
                ->perform($driver->getMigration());
        }

        $storage=$this->storage;
        $this->complexStorage = new Storage(function() use ($storage) {
            $dsn = new Settings();
            $dsn->driverClassName = JsonProxy::className();
            $dsn->proxyClient = $storage;
            return $dsn;
        });


    }

    public function tearDown() {
        if (file_exists($this->sqliteFilename)) {
            $this->db->getDriver()->disconnect();
            unlink($this->sqliteFilename);
        }
    }

}
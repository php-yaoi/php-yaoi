<?php
use Yaoi\Storage;
use Yaoi\Storage\Driver\PhpVarExpire;
use Yaoi\Storage\Settings;


require_once __DIR__ . '/TestStorageBasic.php';

class StoragePhpVarExpireTest extends TestStorageBasic {
    public function setUp() {
        $this->storage = Storage::getInstance(function(){
            $dsn = new Settings();
            $dsn->driverClassName = PhpVarExpire::className();
            return $dsn;
        });
    }

}
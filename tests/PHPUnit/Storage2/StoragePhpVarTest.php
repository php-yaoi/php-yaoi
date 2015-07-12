<?php
use Yaoi\Storage;
use Yaoi\Storage\Driver\PhpVar;
use Yaoi\Storage\Settings;


require_once __DIR__ . '/TestStorageBasic.php';

class StoragePhpVarTest extends TestStorageBasic {
    public function setUp() {
        $this->storage = Storage::getInstance(function(){
            $dsn = new Settings();
            $dsn->driverClassName = PhpVar::className();
            return $dsn;
        });
    }

}
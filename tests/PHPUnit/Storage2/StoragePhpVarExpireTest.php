<?php
use Yaoi\Storage;
use Yaoi\Storage\Driver\PhpVarExpire;
use Yaoi\Storage\Settings;


require_once __DIR__ . '/TestStorageBasic.php';

class StoragePhpVarExpireTest extends TestStorageBasic {
    public function __construct() {
        $this->storage = new Storage(function(){
            $dsn = new Settings();
            $dsn->driverClassName = PhpVarExpire::className();
            return $dsn;
        });
    }

}
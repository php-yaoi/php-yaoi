<?php
use Yaoi\Storage;
use Yaoi\Storage\Driver\PhpVar;
use Yaoi\Storage\Settings;


require_once __DIR__ . '/TestStorageBasic.php';

class StoragePhpVarTest extends TestStorageBasic {
    public function __construct() {
        $this->storage = new Storage(function(){
            $dsn = new Settings();
            $dsn->driverClassName = PhpVar::className();
            return $dsn;
        });
    }

}
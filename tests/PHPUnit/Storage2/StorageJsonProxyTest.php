<?php
use Yaoi\Storage;
use Yaoi\Storage\Driver\JsonProxy;
use Yaoi\Storage\Settings;

require_once __DIR__ . '/StorageSerializeProxyTest.php';

class StorageJsonProxyTest extends StorageSerializeProxyTest {
    protected function initStorage() {
        $base = $this->base;
        $this->storage = new Storage(function () use ($base){
            $dsn = new Settings();
            $dsn->driverClassName = JsonProxy::className();
            $dsn->proxyClient = $base;
            return $dsn;
        });
    }
}
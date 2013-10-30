<?php

/**
 * Class Storage_Driver
 * TODO string only high performance cache
 */
abstract class Storage_Driver {
    /**
     * @var Storage_Dsn
     */
    protected $dsn;
    public function __construct(Storage_Dsn $dsn = null) {
        $this->dsn = $dsn;
    }

    abstract function get($key);
    abstract function set($key, $value, $ttl);
    abstract function delete($key);
    abstract function deleteAll();
}
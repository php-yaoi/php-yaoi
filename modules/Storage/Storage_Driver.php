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

        if (!empty($dsn->staticPropertyRef)) {
            $s = $dsn->staticPropertyRef;
            $s = explode('::$', $s);
            $s[0]::$$s[1] = $this;

        }
    }

    abstract function get($key);
    abstract function keyExists($key);
    abstract function set($key, $value, $ttl);
    abstract function delete($key);
    abstract function deleteAll();
}
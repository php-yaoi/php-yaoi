<?php

use Yaoi\BaseClass;

class Storage_Driver_Null extends BaseClass implements Storage_Driver {
    /**
     * @var Storage_Dsn
     */
    public function __construct(Storage_Dsn $dsn = null)
    {
    }

    public function get($key)
    {
        return false;
    }

    public function keyExists($key)
    {
        return false;
    }

    public function set($key, $value, $ttl)
    {
        return true;
    }

    public function delete($key)
    {
        return true;
    }

    public function deleteAll()
    {
        return true;
    }

}
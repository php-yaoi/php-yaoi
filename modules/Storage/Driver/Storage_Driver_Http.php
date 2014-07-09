<?php

class Storage_Driver_Http implements Storage_Driver {
    /**
     * @var Storage_Dsn
     */
    private $dsn;

    /**
     * @var Http_Client
     */
    private $http;

    public function __construct(Storage_Dsn $dsn = null)
    {
        $this->dsn = $dsn;
        $this->http = new Http_Client();

    }

    private function keyUrl($key) {

    }

    public function get($key)
    {

    }

    public function keyExists($key)
    {
        // TODO: Implement keyExists() method.
    }

    public function set($key, $value, $ttl)
    {
        // TODO: Implement set() method.
    }

    public function delete($key)
    {

    }

    public function deleteAll()
    {
        // TODO: Implement deleteAll() method.
    }

} 
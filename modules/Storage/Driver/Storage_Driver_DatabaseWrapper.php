<?php

class Storage_Driver_DatabaseWrapper extends Storage_Driver {
    /**
     * @var Database_Client
     */
    protected $databaseClient;
    public function connect() {
        $this->dsn;
    }


    function get($key)
    {

        $this->databaseClient->query();
    }

    function keyExists($key)
    {
    }

    function set($key, $value, $ttl)
    {
    }

    function delete($key)
    {
    }

    function deleteAll()
    {
    }

} 
<?php

/**
 * Class Storage_Driver_DatabaseWrapper
 * TODO implement, WIP!
 */

class Storage_Driver_DatabaseWrapper implements  Storage_Driver {
    /**
     * @var Storage_Dsn
     */
    protected $dsn;

    public function __construct(Storage_Dsn $dsn = null)
    {
        $this->dsn = $dsn;
    }

    private $table;
    private $clientId;

    /**
     * @return Database
     */
    private function client() {
        if (null === $this->clientId) {
            $client = Yaoi::db($this->dsn->instanceId);
            $this->clientId = DependencyRepository::add($client);
        }
        return DependencyRepository::$items[$this->clientId];
    }


    function get($key)
    {
        return $this->client()->query("SELECT `val` FROM $this->table WHERE `key` = ?", $key)->fetchRow();
    }

    function keyExists($key)
    {
        return $this->client()->query("SELECT count(1) FROM $this->table WHERE `key` = ?", $key)->fetchRow();
    }

    function set($key, $value, $ttl)
    {
    }

    function delete($key)
    {
        $this->client()->query("DELETE FROM $this->table WHERE `key` = ?", $key);
    }

    function deleteAll()
    {
    }

} 
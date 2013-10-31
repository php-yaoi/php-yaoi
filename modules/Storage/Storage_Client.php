<?php

class Storage_Client implements Storage_KeyValue {
    /**
     * @var Storage_Driver
     */
    protected $driver;

    public function __construct($dsnUrl = null) {
        if (null !== $dsnUrl) {
            $dsn = new Storage_Dsn($dsnUrl);
            $driverClass = 'Storage_Driver_' . String_Utils::toCamelCase($dsn->scheme, '-');
            $this->driver = new $driverClass($dsn);
        }
    }

    public function get($key)
    {
        return $this->driver->get($key);
    }

    public function getIn($key, &$var) {
        $var = $this->get($key);
        return $this;
    }

    public function set($key, $value = null, $ttl = 0)
    {
        $this->driver->set($key, $value, $ttl);
        return $this;
    }

    public function delete($key)
    {
        $this->driver->delete($key);
        return $this;
    }

    public function deleteAll()
    {
        $this->driver->deleteAll();
        return $this;
    }

} 
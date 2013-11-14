<?php

class Storage_Driver_Memcache extends Storage_Driver {

    /**
     * @var Memcache
     */
    protected $memcache;

    protected function connect() {
        $this->memcache = new Memcache();
        $hostname = $this->dsn->unixSocket ? 'unix://' . $this->dsn->unixSocket : $this->dsn->hostname;
        if ($this->dsn->unixSocket) {
            $port = 0;
        }
        else {
            $port = $this->dsn->port
                ? $this->dsn->port
                : ini_get(' memcache.default_port');
            if (!$port) {
                $port = 11211;
            }
        }
        $this->memcache->connect($hostname, $port, $this->dsn->connectionTimeout);
    }

    public function set($key, $value, $ttl) {
        if (null === $this->memcache) {
            $this->connect();
        }
        return $this->memcache->set($key, $value, 0, $ttl);
    }

    function keyExists($key) {
        if (null === $this->memcache) {
            $this->connect();
        }
        if ($this->memcache->add($key, null)) {
            $this->memcache->delete($key);
            return false;
        }
        else {
            return true;
        }
    }

    public function get($key) {
        if (null === $this->memcache) {
            $this->connect();
        }
        return $this->memcache->get($key);
    }

    function delete($key) {
        if (null === $this->memcache) {
            $this->connect();
        }
        return $this->memcache->delete($key);
    }

    function deleteAll() {
        if (null === $this->memcache) {
            $this->connect();
        }
        $this->memcache->flush();
    }

    public function __destruct() {
        if (null !== $this->memcache) {
            $this->memcache->close();
        }
    }

}
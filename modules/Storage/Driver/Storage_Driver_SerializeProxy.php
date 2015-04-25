<?php

class Storage_Driver_SerializeProxy extends Base_Class implements Storage_Driver {
    public function get($key)
    {
        $value = $this->storage->get($key);
        if ($value) {
            return unserialize($value);
        }
        else {
            return null;
        }
    }

    public function keyExists($key)
    {
        return $this->storage->keyExists($key);
    }

    public function set($key, $value, $ttl)
    {
        return $this->storage->set($key, serialize($value), $ttl);
    }

    public function delete($key)
    {
        return $this->storage->delete($key);
    }

    public function deleteAll()
    {
        return $this->storage->deleteAll();
    }

    /**
     * @var Storage
     */
    protected $storage;

    /**
     * @var Storage_Dsn
     */
    public function __construct(Storage_Dsn $dsn = null)
    {
        $this->dsn = $dsn;
        if (empty($dsn->proxyClient)) {
            throw new Storage_Exception('proxyClient required in dsn', Storage_Exception::PROXY_REQUIRED);
        }

        $this->storage = Storage::getInstance($this->dsn->proxyClient);
    }

}
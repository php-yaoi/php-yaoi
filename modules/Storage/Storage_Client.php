<?php

class Storage_Client {
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
        $this->prepareKey($key);
        return $this->driver->get($key);
    }

    public function getIn($key, &$var) {
        $var = $this->get($key);
        return $this;
    }

    /**
     * @param $key
     * @param null $value
     * @param int $ttl
     * @return self
     */
    public function set($key, $value = null, $ttl = 0)
    {
        $this->prepareKey($key);
        $this->driver->set($key, $value, $ttl);
        return $this;
    }

    public function delete($key)
    {
        $this->prepareKey($key);
        $this->driver->delete($key);
        return $this;
    }

    public function deleteAll()
    {
        $this->driver->deleteAll();
        return $this;
    }

    protected function prepareKey(&$key) {
        if (is_array($key) && !($this->driver instanceof Storage_ArrayKey)) {
            $key = implode('/', $key);
        }
    }


    public function getDriver() {
        return $this->driver;
    }

    /**
     * @return array
     * @throws Storage_Exception
     */
    public function exportArray() {
        if (!$this->driver instanceof Storage_ExportArray) {
            throw new Storage_Exception('Export not supported in ' . get_class($this->driver),
                Storage_Exception::EXPORT_ARRAY_NOT_SUPPORTED);
        }

        return $this->driver->exportArray();
    }

} 
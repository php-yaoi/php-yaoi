<?php

/**
 * Class Storage_Client
 * @method Storage_Driver|Storage_ExportImportArray|Storage_ArrayKey getDriver()
 */
class Storage extends Client {
    public static $conf = array();

    public function get($key)
    {
        $this->prepareKey($key);
        return $this->getDriver()->get($key);
    }

    public function keyExists($key) {
        $this->prepareKey($key);
        return $this->getDriver()->keyExists($key);
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
        $this->getDriver()->set($key, $value, $ttl);
        return $this;
    }

    public function delete($key)
    {
        $this->prepareKey($key);
        $this->getDriver()->delete($key);
        return $this;
    }

    public function deleteAll()
    {
        $this->getDriver()->deleteAll();
        return $this;
    }

    protected function prepareKey(&$key) {
        if (is_array($key) && !($this->getDriver() instanceof Storage_ArrayKey)) {
            $key = implode('/', $key);
        }
    }


    /**
     * @return array
     * @throws Storage_Exception
     */
    public function exportArray() {
        if (!$this->getDriver() instanceof Storage_ExportImportArray) {
            throw new Storage_Exception('Export not supported in ' . get_class($this->getDriver()),
                Storage_Exception::EXPORT_ARRAY_NOT_SUPPORTED);
        }

        return $this->getDriver()->exportArray();
    }

    public function importArray($data) {
        if (!$this->getDriver() instanceof Storage_ExportImportArray) {
            throw new Storage_Exception('Export not supported in ' . get_class($this->getDriver()),
                Storage_Exception::EXPORT_ARRAY_NOT_SUPPORTED);
        }

        return $this->getDriver()->importArray($data);

    }

}
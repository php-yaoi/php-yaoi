<?php
/**
 * Created by PhpStorm.
 * User: poturaev
 * Date: 30.10.13
 * Time: 10:31
 */

class Storage_Driver_SerializedFile extends Storage_Driver implements Storage_ArrayKey {

    protected $data;
    protected $fileName;

    protected $loaded = false;
    protected function connect() {
        $this->fileName = $this->dsn->path;

        if (!$this->loaded) {
            if (file_exists($this->fileName)) {
                $this->data = unserialize(file_get_contents($this->fileName));
                if (false === $this->data) {
                    throw new Storage_Exception('Bad serialized data', Storage_Exception::BAD_SERIALIZED_DATA);
                }
            }
            else {
                $this->data = array();
            }
            $this->loaded = true;
        }
    }

    public function set($key, $value, $ttl) {
        if (!$this->loaded) {
            $this->connect();
        }

        $this->data[$key] = $value;
        $this->modified = true;
    }

    public function get($key) {
        if (!$this->loaded) {
            $this->connect();
        }

        if (isset($this->data[$key])) {
            return $this->data[$key];
        }
        else {
            return null;
        }
    }

    function delete($key)
    {
        if (!$this->loaded) {
            $this->connect();
        }

        if (array_key_exists($key, $this->data)) {
            unset($this->data[$key]);
            $this->modified = true;
        }
    }

    function deleteAll()
    {
        $this->data = array();
        $this->saveAll();
    }


    protected $modified = false;
    protected function saveAll() {
        if (!$this->modified) {
            return;
        }
        file_put_contents($this->fileName, serialize($this->data));
        $this->modified = false;
    }

    public function __destruct() {
        $this->saveAll();
    }



}
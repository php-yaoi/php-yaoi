<?php

class Storage_Driver_SerializedFile extends Storage_Driver_PhpVar implements Storage_ArrayKey {

    protected $fileName;
    protected $loaded;

    protected function load() {
        $this->fileName = $this->dsn->path;

        if (file_exists($this->fileName)) {
            if ($this->dsn->compression) {
                //$this->data = @unserialize(file_get_contents($this->fileName));
                $this->data = @unserialize(gzdecode(file_get_contents($this->fileName)));
            }
            else {
                $this->data = @unserialize(file_get_contents($this->fileName));
            }
            if (false === $this->data) {
                throw new Storage_Exception('Bad serialized data', Storage_Exception::BAD_SERIALIZED_DATA);
            }
        }
        else {
            $this->data = array();
        }
        $this->loaded = true;
    }

    public function set($key, $value, $ttl)
    {
        if (!$this->loaded) {
            $this->load();
        }
        parent::set($key, $value, $ttl);
    }

    public function get($key)
    {
        if (!$this->loaded) {
            $this->load();
        }
        return parent::get($key);
    }

    public function delete($key)
    {
        if (!$this->loaded) {
            $this->load();
        }
        parent::delete($key);
    }


    function deleteAll()
    {
        if (!$this->loaded) {
            $this->load();
        }
        parent::deleteAll();
        $this->saveAll();
    }


    protected function saveAll() {
        if (!$this->modified) {
            return;
        }
        if ($this->dsn->compression) {
            file_put_contents($this->fileName, gzencode(serialize($this->data)));
        }
        else {
            file_put_contents($this->fileName, serialize($this->data));
        }
        $this->modified = false;
    }

    public function __destruct() {
        $this->saveAll();
    }
}
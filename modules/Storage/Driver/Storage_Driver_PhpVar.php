<?php

class Storage_Driver_PhpVar extends Storage_Driver implements Storage_ArrayKey {
    protected $data = array();
    protected $loaded = true;
    protected $modified = false;

    public function set($key, $value, $ttl) {
        if (is_array($key)) {
            $kk =& $this->data;
            foreach ($key as $k) {
                $kk =& $kk[$k];
            }
            $kk = $value;
        }
        else {
            $this->data[$key] = $value;
        }

        $this->modified = true;
    }

    public function get($key) {
        if (is_array($key)) {
            $kk =& $this->data;
            foreach ($key as $k) {
                if (array_key_exists($k, $kk)) {
                    $kk =& $kk[$k];
                }
                else {
                    return null;
                }
            }
            return $kk;
        }
        elseif (isset($this->data[$key])) {
            return $this->data[$key];
        }
        elseif (null === $key) {
            return $this->data;
        }
        else {
            return null;
        }
    }

    public function delete($key)
    {
        if (is_array($key)) {
            $kk =& $this->data;
            $lastKey = null;
            $parent = null;
            foreach ($key as $k) {
                $parent =& $kk;
                $lastKey = $k;
                if (array_key_exists($k, $kk)) {
                    $kk =& $kk[$k];
                }
                else {
                    return;
                }
            }
            if ($parent && array_key_exists($lastKey, $parent)) {
                unset($parent[$lastKey]);
                $this->modified = true;
            }
        }
        elseif (array_key_exists($key, $this->data)) {
            unset($this->data[$key]);
            $this->modified = true;
        }
    }

    public function deleteAll()
    {
        $this->data = array();
    }

}
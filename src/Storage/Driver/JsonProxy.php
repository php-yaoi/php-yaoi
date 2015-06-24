<?php

namespace Yaoi\Storage\Driver;

class JsonProxy extends SerializeProxy
{
    public function get($key)
    {
        return json_decode($this->storage->get($key), true);
    }


    public function set($key, $value, $ttl)
    {
        return $this->storage->set($key, json_encode($value), $ttl);
    }
}
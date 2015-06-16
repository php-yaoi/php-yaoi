<?php

namespace Yaoi\Storage;

use Closure;
use Yaoi\Storage;

class Null extends Storage
{
    public function __construct()
    {
    }

    public function get($key, Closure $setOnMiss = null, $ttl = null)
    {
        return null;
    }

    public function keyExists($key)
    {
        return false;
    }

    public function getIn($key, &$var)
    {
        $var = null;
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
        return $this;
    }

    public function delete($key)
    {
        return $this;
    }

    public function deleteAll()
    {
        return $this;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function exportArray()
    {
        return array();
    }

    public function importArray($data)
    {
    }

}
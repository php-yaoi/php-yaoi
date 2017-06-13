<?php

namespace Yaoi\Storage\Driver;

use Yaoi\Storage\Contract\Driver;
use Yaoi\Storage\Settings;

class ProxySerialize implements Driver
{
    /**
     * @var Settings
     */
    public function __construct(Settings $dsn = null)
    {
        // TODO: Implement __construct() method.
    }

    public function get($key)
    {
        // TODO: Implement get() method.
    }

    public function keyExists($key)
    {
        // TODO: Implement keyExists() method.
    }

    public function set($key, $value, $ttl)
    {
        // TODO: Implement set() method.
    }

    public function delete($key)
    {
        // TODO: Implement delete() method.
    }

    public function deleteAll()
    {
        // TODO: Implement deleteAll() method.
    }

}
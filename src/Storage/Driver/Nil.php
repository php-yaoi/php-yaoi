<?php

namespace Yaoi\Storage\Driver;

use Yaoi\BaseClass;
use Yaoi\Storage\Contract\Driver;
use Yaoi\Storage\Settings;

class Nil extends BaseClass implements Driver
{
    /**
     * @var Settings
     */
    public function __construct(Settings $dsn = null)
    {
    }

    public function get($key)
    {
        return false;
    }

    public function keyExists($key)
    {
        return false;
    }

    public function set($key, $value, $ttl)
    {
        return true;
    }

    public function delete($key)
    {
        return true;
    }

    public function deleteAll()
    {
        return true;
    }

}
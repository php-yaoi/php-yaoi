<?php

namespace Yaoi\Storage\Driver;

use Yaoi\Storage\Contract\Driver;
use Yaoi\BaseClass;
use Yaoi\Storage\Dsn;

class Null extends BaseClass implements Driver
{
    /**
     * @var Dsn
     */
    public function __construct(Dsn $dsn = null)
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
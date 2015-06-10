<?php

use Yaoi\Date\Source;

class Storage_Driver_PhpVarExpire extends Storage_Driver_PhpVar implements Storage_Expire
{
    /**
     * @var Source
     */
    protected $time;

    /**
     * @var Storage_Driver_PhpVar
     */
    protected $expire;

    public function __construct(Storage_Dsn $dsn = null) {
        $this->dsn = $dsn ? $dsn : new Storage_Dsn();
        $this->expire = new Storage_Driver_PhpVar();
        $this->time = Yaoi::time($this->dsn->dateSource); // TODO use getInstance after Date_Source refactoring
    }

    public function set($key, $value, $ttl)
    {
        if ($ttl && $ttl < 30*86400) {
            $ttl = $this->time->now() + $ttl;
        }

        if ($ttl) {
            $this->expire->set($key, $ttl, 0);
        }

        parent::set($key, $value, $ttl);
    }

    public function get($key)
    {
        $value = parent::get($key);
        if (null !== $value) {
            $expire = $this->expire->get($key);
            if ($expire && $expire < $this->time->now()) {
                $this->delete($key);
                return null;
            }
            return $value;
        }

        return $value;
    }

    public function keyExists($key) {
        $this->get($key);
        return parent::keyExists($key);
    }
}
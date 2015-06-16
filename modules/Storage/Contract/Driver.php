<?php
namespace Yaoi\Storage\Contract;

use Yaoi\Storage\Dsn;

/**
 * Class Storage_Driver
 * TODO string only high performance cache
 */
interface Driver
{
    /**
     * @var Dsn
     */
    /*
    protected $dsn;
    public function __construct(Storage_Dsn $dsn = null) {
        $this->dsn = $dsn;

        if (!empty($dsn->staticPropertyRef)) {
            $s = $dsn->staticPropertyRef;
            $s = explode('::$', $s);
            $s[0]::$$s[1] = $this;

        }
    }
    */

    public function __construct(Dsn $dsn = null);

    public function get($key);

    public function keyExists($key);

    public function set($key, $value, $ttl);

    public function delete($key);

    public function deleteAll();
}
<?php
namespace Yaoi;

use Yaoi\Client\Dsn;
use Yaoi\Client\Exception;
use Closure;
use Yaoi\Database\Driver;
use String_Dsn;
use String_Utils;
use Yaoi\BaseClass;


/**
 * Class Client
 * @property array $conf
 * @property array $instances
 */
abstract class Client extends BaseClass
{
    static protected $dsnClass = 'Client_Dsn';

    /**
     * @param null $dsn
     * @return null|Dsn
     * @throws Exception
     */
    public static function dsn($dsn = null)
    {
        if ($dsn instanceof Closure) {
            $dsn = $dsn();
            if (!$dsn instanceof Dsn) {
                throw new Exception('Closure should return dsn instance', Exception::DSN_REQUIRED);
            }
        }

        if (null === $dsn || is_string($dsn)) {
            /**
             * @see Client_Dsn descendants
             */
            $class = static::$dsnClass;
            if (null === $dsn) {
                $dsn = null;
            } else {
                $dsn = new $class($dsn);
            }
        } elseif (!$dsn instanceof String_Dsn) {
            throw new Exception('Invalid argument', Exception::INVALID_ARGUMENT);
        }
        return $dsn;
    }


    /**
     * @param String_Dsn|string|Closure|null $dsn
     * @throws Exception
     */
    public function __construct($dsn = null)
    {
        $this->dsn = static::dsn($dsn);
    }


    /**
     * @param string $id
     * @param Dsn $originalId
     * @return static
     * @throws Exception
     */
    private static function createByConfId($id = 'default')
    {
        if (isset(static::$conf[$id])) {
            $dsn = static::dsn(static::$conf[$id]);
            $resource = new static($dsn);
        } elseif ('default' === $id) {
            throw new Exception('Default ' . get_called_class() . ' not configured',
                Exception::DEFAULT_NOT_SET);
        } else {
            $resource = static::createByConfId('default', $id);
        }
        return $resource;
    }


    /**
     * Returns client instance
     *
     *
     * @param string|Client|Dsn|Closure $id
     * @param bool $reuse return previously created instance if true, create new if false
     * @return static
     * @throws Exception
     * fallback instead default
     */
    public static function getInstance($id = 'default', $reuse = true)
    {
        if (is_string($id)) {
            if ($reuse) {
                if (!isset(static::$conf[$id])) {
                    $id = 'default';
                }

                $resource = &static::$instances[$id];
                if (!isset($resource)) {
                    $resource = static::createByConfId($id);
                }
            } else {
                $resource = static::createByConfId($id);
            }

            return $resource;
        } elseif ($id instanceof Client) {
            return $id;
        } elseif ($id instanceof String_Dsn || $id instanceof Dsn || $id instanceof Closure) {
            return new static($id);
        } else {
            throw new Exception('Invalid argument, Client/Closure/Client_Dsn/string required', Exception::INVALID_ARGUMENT);
        }
    }


    protected $dsn;

    private $driver;

    /**
     * @return Driver
     * @throws Exception
     */
    public function getDriver()
    {
        if (null === $this->driver) {
            if ($this->dsn && $this->dsn->driverClassName) {
                $driverClass = $this->dsn->driverClassName;
            } else {
                $driverClass = get_called_class() . '_Driver_' . String_Utils::toCamelCase($this->dsn->scheme, '-');
            }
            if (!class_exists($driverClass)) {
                throw new Exception($driverClass . ' (' . $this->dsn->scheme . ') not found', Exception::NO_DRIVER);
            }
            $this->driver = new $driverClass($this->dsn);
        }

        /*
        if ($this->driver instanceof Migration_Required) {
            Migration_Manager::getInstance()->perform($this->driver->getMigration());
        }
        */

        return $this->driver;
    }

    protected function forceDriver($driver)
    {
        $this->driver = $driver;
    }


}
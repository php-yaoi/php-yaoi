<?php
namespace Yaoi;

use Yaoi\Service\Exception;
use Closure;
use Yaoi\Database\Driver;
use Yaoi\String\Utils;
use Yaoi\Service\Dsn;


/**
 * @property array $conf
 * @property array $instances
 */
abstract class Service extends BaseClass
{
    static protected $dsnClass = '\Yaoi\Service\Dsn';

    /**
     * @param null $dsn
     * @return null|Service\Dsn
     * @throws Exception
     */
    public static function dsn($dsn = null)
    {
        if ($dsn instanceof Closure) {
            $dsn = $dsn();
            if (!$dsn instanceof Dsn) {
                throw new Service\Exception('Closure should return dsn instance', Service\Exception::DSN_REQUIRED);
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
        } elseif (!$dsn instanceof Dsn) {
            throw new Service\Exception('Invalid argument', Service\Exception::INVALID_ARGUMENT);
        }
        return $dsn;
    }


    /**
     * @param Dsn|string|Closure|null $dsn
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
    private static function createByConfId($id = 'default', $previousId = null)
    {
        if (isset(static::$conf[$id])) {
            $dsn = static::dsn(static::$conf[$id]);
            $dsn->previousId = $previousId;
            $resource = new static($dsn);
        } elseif ('default' === $id) {
            throw new Service\Exception('Default ' . get_called_class() . ' not configured',
                Service\Exception::DEFAULT_NOT_SET);
        } else {
            $resource = static::createByConfId('default', $id);
        }
        return $resource;
    }


    /**
     * Returns client instance
     *
     *
     * @param string|Service|Dsn|Closure $id
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
        } elseif ($id instanceof Service) {
            return $id;
        } elseif ($id instanceof Dsn || $id instanceof Dsn || $id instanceof Closure) {
            return new static($id);
        } else {
            throw new Service\Exception('Invalid argument, Service/Closure/Service\Dsn/string required', Service\Exception::INVALID_ARGUMENT);
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
                $driverClass = get_called_class() . '\Driver\\' . Utils::toCamelCase($this->dsn->scheme, '-');
            }
            if (!class_exists($driverClass)) {
                throw new Service\Exception($driverClass . ' (' . $this->dsn->scheme . ') not found', Service\Exception::NO_DRIVER);
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
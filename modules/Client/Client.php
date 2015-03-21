<?php


/**
 * Class Client
 * @property array $conf
 * @property array $instances
 */
abstract class Client extends Base_Class {
    static protected $dsnClass = 'Client_Dsn';

    /**
     * @param null $dsn
     * @return null|Client_Dsn
     * @throws Client_Exception
     */
    public static function dsn($dsn = null) {
        if ($dsn instanceof Closure) {
            $dsn = $dsn();
        }

        if (null === $dsn || is_string($dsn)) {
            /**
             * @see Client_Dsn descendants
             */
            $class = static::$dsnClass;
            if (null === $dsn) {
                $dsn = null;
            }
            else {
                $dsn = new $class($dsn);
            }
        }
        elseif (!$dsn instanceof String_Dsn) {
            throw new Client_Exception('Invalid argument', Client_Exception::INVALID_ARGUMENT);
        }
        return $dsn;
    }


    /**
     * @param String_Dsn|string|Closure|null $dsn
     * @throws Client_Exception
     */
    public function __construct($dsn = null) {
        $this->dsn = static::dsn($dsn);
    }


    /**
     * @param string $id
     * @param Client_Dsn $originalId
     * @return static
     * @throws Client_Exception
     */
    private static function createByConfId($id = 'default') {
        if (isset(static::$conf[$id])) {
            $dsn = static::dsn(static::$conf[$id]);
            $resource = new static($dsn);
        }
        elseif ('default' === $id) {
            throw new Client_Exception('Default ' . get_called_class() . ' not configured',
                Client_Exception::DEFAULT_NOT_SET);
        }
        else {
            $resource = static::createByConfId('default', $id);
        }
        return $resource;
    }


    /**
     * Returns client instance
     *
     *
     * @param string|Client|Client_Dsn|Closure $id
     * @param bool $reuse return previously created instance if true, create new if false
     * @return static
     * @throws Client_Exception
     */
    public static function getInstance($id = 'default', $reuse = true) {
        if (is_string($id)) {
            if ($reuse) {
                if (!isset(static::$conf[$id])) {
                    $id = 'default';
                }

                $resource = &static::$instances[$id];
                if (!isset($resource)) {
                    $resource = static::createByConfId($id);
                }
            }
            else {
                $resource = static::createByConfId($id);
            }

            return $resource;
        }

        if ($id instanceof Client) {
            return $id;
        }

        if ($id instanceof String_Dsn || $id instanceof Client_Dsn || $id instanceof Closure) {
            return new static($id);
        }
    }



    protected $dsn;

    private $driver;

    /**
     * @return Database_Driver
     * @throws Client_Exception
     */
    public function getDriver() {
        if (null === $this->driver) {
            $driverClass = get_called_class() . '_Driver_' . String_Utils::toCamelCase($this->dsn->scheme, '-');
            if (!class_exists($driverClass)) {
                throw new Client_Exception($driverClass . ' (' . $this->dsn->scheme . ') not found', Client_Exception::NO_DRIVER);
            }
            $this->driver = new $driverClass($this->dsn);
        }

        return $this->driver;
    }

    protected function forceDriver($driver) {
        $this->driver = $driver;
    }


}
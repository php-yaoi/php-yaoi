<?php

abstract class Client extends Base_Class {
    //public static $conf = array();

    /**
     * @param null $dsn
     * @return null|String_Dsn
     * @throws Client_Exception
     */
    public static function dsn($dsn = null) {
        if ($dsn instanceof Closure) {
            $dsn = $dsn();
        }

        if (null === $dsn || is_string($dsn)) {
            /**
             * @see String_Dsn descendants
             */
            $class = get_called_class() . '_Dsn';
            $dsn = new $class($dsn);
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
     * @param null $originalId
     * @return static
     * @throws Client_Exception
     */
    private static function createByConfId($id = 'default', $originalId = null) {
        if (isset(static::$conf[$id])) {
            $dsn = static::dsn(static::$conf[$id]);
            if ($originalId) {
                $dsn->originalId = $originalId;
            }
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

    protected static $instances = array();

    /**
     * @param string $id
     * @param bool $reuse
     * @return static
     * @throws Client_Exception
     */
    public static function getInstance($id = 'default', $reuse = true) {
        if (is_string($id)) {
            if ($reuse) {
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

        if ($id instanceof String_Dsn || $id instanceof Closure) {
            return new static($id);
        }

        throw new Client_Exception('Invalid argument', Client_Exception::INVALID_ARGUMENT);
    }



    protected $dsn;

    private $driver;
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
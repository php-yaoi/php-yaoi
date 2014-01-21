<?php

abstract class Client extends Base_Class {
    public static $conf = array();

    public function __construct(String_Dsn $dsn = null) {
        $this->dsn = $dsn;
    }


    /**
     * @param string $id
     * @param null $originalId
     * @return static
     * @throws Client_Exception
     */
    public static function createByConfId($id = 'default', $originalId = null) {
        if (isset(static::$conf[$id])) {
            $dsn = static::$conf[$id];
            if ($originalId) {
                $dsn->originalId = $originalId;
            }
            $resource = static::createByDsn($dsn);
        }
        elseif ('default' == $id) {
            throw new Client_Exception('Default ' . get_called_class() . ' not configured',
                Client_Exception::DEFAULT_NOT_SET);
        }
        else {
            $resource = static::createByConfId('default', $id);
        }
        return $resource;
    }

    /**
     * @param String_Dsn $dsn
     * @return static
     * @throws Client_Exception
     */
    public static function createByDsn($dsn) {
        if (!$dsn instanceof String_Dsn) {
            if ($dsn instanceof Closure) {
                $dsn = $dsn();
            }
            else {
                $dsn = new String_Dsn($dsn);
            }
        }

        $resource = new static($dsn);
        return $resource;
    }


    private static $instances = array();

    /**
     * @param string $id
     * @return static
     */
    public static function getInstance($id = 'default') {
        $resource = &self::$instances[$id];
        if (!isset($resource)) {
            $resource = static::createByConfId($id);
        }
        return $resource;
    }

    protected $dsn;

    private $driver;
    protected function getDriver() {
        if (null === $this->driver) {
            if (null === $this->dsn) {
                return null;
            }
            $driverClass = get_called_class() . '_Driver_' . String_Utils::toCamelCase($this->dsn->scheme, '-');
            if (!class_exists($driverClass)) {
                throw new Client_Exception($driverClass . ' (' . $this->dsn->scheme . ') not found', Client_Exception::NO_DRIVER);
            }
            $this->driver = new $driverClass($this->dsn);
        }

        return $this->driver;
    }


}
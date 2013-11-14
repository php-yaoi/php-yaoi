<?php

abstract class Database_Abstract_Client extends Base_Class implements Mock_Able {
    /**
     * @var Database_Driver
     */
    protected $driver;

    public function __construct($dsnUrl = null) {
        if (null !== $dsnUrl) {
            $dsn = new Database_Dsn($dsnUrl);
            $driverClass = 'Database_Driver_' . String_Utils::toCamelCase($dsn->scheme, '-');
            if (!class_exists($driverClass)) {
                throw new Database_Exception('Driver for ' . $dsn->scheme . ' not found', Database_Exception::NO_DRIVER);
            }
            $this->driver = new $driverClass($dsn);
        }
    }

    /**
     * @param null $statement
     * @param null $binds
     * @return Database_Query
     */
    public function query($statement = null, $binds = null) {
        $query = new Database_Query($statement, $binds, $this);
        return $query;
    }

    public function getDriver() {
        return $this->driver;
    }

    public function quote($s) {
        return $this->driver->quote($s);
    }


    public function insert() {
        return new Database_Insert($this);
    }

    /*
    public function select() {
        // TODO implement
        //return new Database_Select($this);
    }

    public function delete() {
        // TODO implement
        //return new Databse_Delete($this);
    }


    public function update() {
        // TODO implement
        //return new Database_Update($this);
    }

    */

    protected $originalDriver;
    public function mock(Mock_DataSet $dataSet = null)
    {
        if ($dataSet instanceof Mock_DataSetPlay) {
            $driver = new Database_Mock_DriverPlay();
            $this->originalDriver = $this->driver;
            $driver->mock($dataSet);
            $this->driver = $driver;
        }
        elseif ($dataSet instanceof Mock_DataSetCapture) {
            $driver = new Database_Mock_DriverCapture();
            if ($this->originalDriver) {
                $this->driver = $this->originalDriver;
            }
            $driver->setOriginalDriver($this->driver);
            $this->originalDriver = $this->driver;
            $driver->mock($dataSet);
            $this->driver = $driver;
        }
        elseif (null === $dataSet && null !== $this->originalDriver) {
            $this->driver = $this->originalDriver;
        }
    }

    public static function createById($id = 'default') {
        if (!isset($resource)) {
            if (isset(Database_Conf::$dsn[$id])) {
                $resource = new Database_Client(Database_Conf::$dsn[$id]);
            }
            elseif ('default' == $id) {
                throw new Database_Exception('Default database connection not configured', Database_Exception::DEFAULT_NOT_SET);
            }
            else {
                $resource = self::createById('default');
            }
        }
        return $resource;
    }
}
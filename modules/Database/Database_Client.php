<?php

class Database_Client extends Base_Class implements Mock_Able {
    /**
     * @var Database_Driver
     */
    protected $driver;

    public function __construct($dsn = null) {
        if (null !== $dsn) {
            if (!$dsn instanceof Database_Dsn) {
                $dsn = new Database_Dsn($dsn);
            }

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
        if (func_num_args() > 2) {
            $arguments = func_get_args();
            array_shift($arguments);
            $binds = $arguments;
        }
        if (null !== $binds && !is_array($binds)) {
            $binds = array($binds);
        }
        $query = new Database_Query($statement, $binds, $this->driver);
        if (null !== $this->log) {
            $query->log($this->log);
        }

        return $query;
    }


    public function mappableInsertString(Mappable $item) {
        $l = array_map(array($this, 'quote'), $item->toArray());
        return "(".implode(',', array_keys($l)).") VALUES (" . implode(",", $l) . ")";
    }

    public function getDriver() {
        return $this->driver;
    }

    public function quote($s) {
        return $this->driver->quote($s);
    }


    protected $originalDriver;

    /**
     * @param Mock_DataSet $dataSet
     * @return Database_Client
     */
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
        return $this;
    }

    public static function createById($id = 'default') {
        if (isset(Database_Conf::$dsn[$id])) {
            $resource = new Database_Client(Database_Conf::$dsn[$id]);
        }
        elseif ('default' == $id) {
            throw new Database_Exception('Default database connection not configured', Database_Exception::DEFAULT_NOT_SET);
        }
        else {
            $resource = self::createById('default');
        }
        return $resource;
    }

    /**
     * @var Log
     */
    private $log;
    public function log(Log $log = null) {
        $this->log = $log;
    }

}
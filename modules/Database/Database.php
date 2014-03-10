<?php

class Database extends Client implements Mock_Able {

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
        $query = new Database_Query($statement, $binds, $this->getDriver());
        if (null !== $this->log) {
            $query->log($this->log);
        }

        return $query;
    }


    public function mappableInsertString(Mappable $item) {
        $l = array_map(array($this, 'quote'), $item->toArray());
        return "(".implode(',', array_keys($l)).") VALUES (" . implode(",", $l) . ")";
    }


    public function quote($s) {
        return $this->getDriver()->quote($s);
    }


    protected $originalDriver;

    /**
     * @param Mock_DataSet $dataSet
     * @return Database
     */
    public function mock(Mock_DataSet $dataSet = null)
    {
        if ($dataSet instanceof Mock_DataSetPlay) {
            $driver = new Database_Mock_DriverPlay();
            if (null === $this->originalDriver) {
                $this->originalDriver = $this->getDriver();
            }
            $driver->mock($dataSet);
            $this->forceDriver($driver);
        }
        elseif ($dataSet instanceof Mock_DataSetCapture) {
            $driver = new Database_Mock_DriverCapture();
            if ($this->originalDriver) {
                $this->forceDriver($this->originalDriver);
            }
            $driver->setOriginalDriver($this->getDriver());
            if (null === $this->originalDriver) {
                $this->originalDriver = $this->getDriver();
            }
            $driver->mock($dataSet);
            $this->forceDriver($driver);
        }
        elseif (null === $dataSet && null !== $this->originalDriver) {
            $this->forceDriver($this->originalDriver);
            $this->originalDriver = null;
        }
        return $this;
    }

    public static function createById($id = 'default') {
        if (isset(Database_Conf::$dsn[$id])) {
            $resource = new Database(Database_Conf::$dsn[$id]);
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
        return $this;
    }

}
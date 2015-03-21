<?php

/**
 * TODO catch and repair crashed table
 * TODO reconnect on gone away
 *
 * Class Database
 * @property Database_Dsn $dsn
 */
class Database extends Client implements Database_Interface {
    protected static $dsnClass = 'Database_Dsn';

    public static $conf = array();
    protected static $instances = array();

    /**
     * @param string|Database_Dsn $dsn
     */
    public function __construct($dsn = null) {
        parent::__construct($dsn);
        if ($this->dsn->logQueries) {
            $this->log(Log::getInstance($this->dsn->logQueries));
        }
    }


    /**
     * @param null $statement
     * @param null $binds
     * @return Database_Query
     */
    public function query($statement = null, $binds = null) {
        $arguments = func_get_args();

        $query = new Database_Query(Sql_Expression::createFromFuncArguments($arguments), $this->getDriver());
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

    public function symbol($s) {
        return $this->getDriver()->quoteSymbol($s);
    }

    /**
     * @return integer
     */
    public function lastInsertId()
    {
        return $this->getDriver()->lastInsertId();
    }


    public function disconnect() {
        $this->getDriver()->disconnect();
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

    /**
     * @var Log
     */
    private $log;
    public function log(Log $log = null) {
        $this->log = $log;
        return $this;
    }


    /**
     * @param $expression
     * @param null $binds
     * @return Sql_Expression
     * @throws Sql_Exception
     */
    public function expr($expression, $binds = null) {
        return Sql_Expression::createFromFuncArguments(func_get_args());
    }


    /**
     * @param null $from
     * @return Sql_SelectInterface
     */
    public function select($from = null) {
        $select = new Sql_Statement();
        $select
            ->bindDatabase($this)
            ->select();

        if (null !== $from) {
            $select->from($from);
        }
        return $select;
    }

    /**
     * @param null $from
     * @return Sql_DeleteInterface
     */
    public function delete($from = null)
    {
        $delete = new Sql_Statement();
        $delete
            ->bindDatabase($this)
            ->delete($from);

        return $delete;
    }

    /**
     * @param null $table
     * @return Sql_UpdateInterface
     */
    public function update($table = null)
    {
        $update = new Sql_Statement();
        $update
            ->bindDatabase($this)
            ->update($table);

        return $update;
    }

    /**
     * @param null $table
     * @return Sql_InsertInterface
     */
    public function insert($table = null)
    {
        $insert = new Sql_Statement();
        $insert
            ->bindDatabase($this)
            ->insert($table);

        return $insert;
    }


    /**
     * @return Sql_Statement
     */
    public function statement() {
        return Sql_Statement::create()->bindDatabase($this);
    }

}
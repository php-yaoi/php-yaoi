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
     * @param Mock $dataSet
     * @return Database
     */
    public function mock(Mock $dataSet = null)
    {
        $driver = $this->getDriver();

        if (null === $dataSet) {
            if ($driver instanceof Database_Driver_MockProxy) {
                $this->forceDriver($driver->driver);
            }
        }
        else {
            if ($driver instanceof Database_Driver_MockProxy) {
                $driver->mock($dataSet);
            }
            else {
                $mockDriver = new Database_Driver_MockProxy($this->dsn);
                $mockDriver->mock($dataSet);
                $mockDriver->driver = $driver;
                $this->forceDriver($mockDriver);
            }
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


    const COLUMN_TYPE_INTEGER = 'int';
    const COLUMN_TYPE_TIMESTAMP = 'datetime';
    const COLUMN_TYPE_STRING = 'string';
    const COLUMN_TYPE_FLOAT = 'float';
    const COLUMN_TYPE_AUTO = 'auto';

    /**
     * @param $tableName
     * @return Database_Definition_Table
     * @throws Client_Exception
     */
    public function getTableDefinition($tableName) {
        return $this->getUtility()->getTableDefinition($tableName);
    }

    const DIALECT_MYSQL = 'Mysql';
    const DIALECT_SQLITE = 'Sqlite';
    const DIALECT_POSTGRESQL = 'Pgsql';
    public function getDialect() {
        return $this->getDriver()->getDialect();
    }

    private $utility;
    public function getUtility() {
        if (null === $this->utility) {
            $this->utility = $this->getDriver()->getUtility();
            $this->utility->setDatabase($this);
        }
        return $this->utility;
    }
}
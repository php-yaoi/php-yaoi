<?php
namespace Yaoi;

use Yaoi\Database\Definition\Table;
use Yaoi\Database\Driver\MockProxy;
use Yaoi\Database\Dsn;
use Yaoi\Database\Contract as DatabaseContract;
use Yaoi\Database\Query;
use Yaoi\Log;
use Yaoi\Mappable\Contract;
use Mock;
use Sql_DeleteInterface;
use Sql_Exception;
use Sql_Expression;
use Sql_InsertInterface;
use Sql_SelectInterface;
use Sql_Statement;
use Sql_UpdateInterface;
use Yaoi\Client;
use Yaoi\Client\Exception;

/**
 * TODO catch and repair crashed table
 * TODO reconnect on gone away
 *
 * Class Database
 * @property Dsn $dsn
 */
class Database extends Client implements DatabaseContract
{
    protected static $dsnClass = '\Yaoi\Database\Dsn';

    public static $conf = array();
    protected static $instances = array();

    /**
     * @param string|Dsn $dsn
     */
    public function __construct($dsn = null)
    {
        parent::__construct($dsn);
        if ($this->dsn->logQueries) {
            $this->log(Log::getInstance($this->dsn->logQueries));
        }
    }


    /**
     * @param null $statement
     * @param null $binds
     * @return Query
     */
    public function query($statement = null, $binds = null)
    {
        $arguments = func_get_args();

        $query = new Query(Sql_Expression::createFromFuncArguments($arguments), $this->getDriver());
        if (null !== $this->log) {
            $query->log($this->log);
        }

        return $query;
    }


    public function mappableInsertString(Contract $item)
    {
        $l = array_map(array($this, 'quote'), $item->toArray());
        return "(" . implode(',', array_keys($l)) . ") VALUES (" . implode(",", $l) . ")";
    }


    public function quote($s)
    {
        return $this->getDriver()->quote($s);
    }

    public function symbol($s)
    {
        return $this->getDriver()->quoteSymbol($s);
    }

    /**
     * @return integer
     */
    public function lastInsertId()
    {
        return $this->getDriver()->lastInsertId();
    }


    public function disconnect()
    {
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
            if ($driver instanceof MockProxy) {
                $this->forceDriver($driver->driver);
            }
        } else {
            if ($driver instanceof MockProxy) {
                $driver->mock($dataSet);
            } else {
                $mockDriver = new MockProxy($this->dsn);
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

    public function log(Log $log = null)
    {
        $this->log = $log;
        return $this;
    }


    /**
     * @param $expression
     * @param null $binds
     * @return Sql_Expression
     * @throws Sql_Exception
     */
    public function expr($expression, $binds = null)
    {
        return Sql_Expression::createFromFuncArguments(func_get_args());
    }


    /**
     * @param null $from
     * @return Sql_SelectInterface
     */
    public function select($from = null)
    {
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
    public function statement()
    {
        return Sql_Statement::create()->bindDatabase($this);
    }


    /**
     * @param $tableName
     * @return Table
     * @throws Exception
     */
    public function getTableDefinition($tableName)
    {
        return $this->getUtility()->getTableDefinition($tableName);
    }

    const DIALECT_MYSQL = 'Mysql';
    const DIALECT_SQLITE = 'Sqlite';
    const DIALECT_POSTGRESQL = 'Pgsql';

    public function getDialect()
    {
        return $this->getDriver()->getDialect();
    }

    private $utility;

    public function getUtility()
    {
        if (null === $this->utility) {
            $this->utility = $this->getDriver()->getUtility();
            $this->utility->setDatabase($this);
        }
        return $this->utility;
    }
}
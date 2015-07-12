<?php
namespace Yaoi;

use Yaoi\Database\Definition\Table;
use Yaoi\Database\Driver\MockProxy;
use Yaoi\Database\Settings;
use Yaoi\Database\Contract as DatabaseContract;
use Yaoi\Database\Query;
use Yaoi\Log;
use Yaoi\Mappable\Contract;
use Yaoi\Mock;
use Yaoi\Sql\DeleteInterface;
use Yaoi\Sql\Expression;
use Yaoi\Sql\InsertInterface;
use Yaoi\Sql\SelectInterface;
use Yaoi\Sql\Statement;
use Yaoi\Sql\UpdateInterface;
use Yaoi\Service;

/**
 * TODO catch and repair crashed table
 * TODO reconnect on gone away
 *
 * Class Database
 * @property Settings $settings
 */
class Database extends Service implements DatabaseContract
{
    /**
     * @param string|Settings $settings
     */
    public function __construct($settings = null)
    {
        parent::__construct($settings);
        if ($this->settings->logQueries) {
            $this->log(Log::getInstance($this->settings->logQueries));
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

        $query = new Query(Expression::createFromFuncArguments($arguments), $this->getDriver());
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
                $this->driver = $driver->driver;
            }
        } else {
            if ($driver instanceof MockProxy) {
                $driver->mock($dataSet);
            } else {
                $mockDriver = new MockProxy($this->settings);
                $mockDriver->mock($dataSet);
                $mockDriver->driver = $driver;
                $this->driver = $mockDriver;
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
     * @return Expression
     * @throws Sql\Exception
     */
    public function expr($expression, $binds = null)
    {
        return Expression::createFromFuncArguments(func_get_args())->bindDatabase($this);
    }


    /**
     * @param null $from
     * @return SelectInterface
     */
    public function select($from = null)
    {
        $select = new Statement();
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
     * @return DeleteInterface
     */
    public function delete($from = null)
    {
        $delete = new Statement();
        $delete
            ->bindDatabase($this)
            ->delete($from);

        return $delete;
    }

    /**
     * @param null $table
     * @return UpdateInterface
     */
    public function update($table = null)
    {
        $update = new Statement();
        $update
            ->bindDatabase($this)
            ->update($table);

        return $update;
    }

    /**
     * @param null $table
     * @return InsertInterface
     */
    public function insert($table = null)
    {
        $insert = new Statement();
        $insert
            ->bindDatabase($this)
            ->insert($table);

        return $insert;
    }


    /**
     * @return Statement
     */
    public function statement()
    {
        return Statement::create()->bindDatabase($this);
    }


    /**
     * @param $tableName
     * @return Table
     * @throws Database\Exception
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

    protected static function getSettingsClassName()
    {
        return Settings::className();
    }

}
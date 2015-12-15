<?php

namespace Yaoi\Database\Driver;

use Yaoi\Database\Driver;
use Yaoi\Sql\Symbol;
use SQLite3;
use SQLite3Result;
use Yaoi\Database;
use Yaoi\Undefined;

class Sqlite extends Driver
{

    /**
     * @var SQLite3
     */
    private $dbHandle;

    private function connect()
    {
        if (null === $this->dbHandle) {
            $this->dbHandle = new SQLite3($this->dsn->path);
        }
    }


    public function query($statement)
    {
        if (null === $this->dbHandle) {
            $this->connect();
        }
        return @$this->dbHandle->query($statement);
    }

    public function lastInsertId()
    {
        return $this->dbHandle->lastInsertRowID();
    }

    public function rowsAffected($result)
    {
        return $this->dbHandle->changes();
    }

    public function escape($value)
    {
        if (null === $this->dbHandle) {
            $this->connect();
        }
        if ($value instanceof Undefined) {
            $value = null;
        }

        return $this->dbHandle->escapeString($value);
    }

    /**
     * @param SQLite3Result $result
     */
    public function rewind($result)
    {
        $result->reset();
    }

    /**
     * @param SQLite3Result $result
     * @return array|false
     */
    public function fetchAssoc($result)
    {
        $row = $result->fetchArray(SQLITE3_ASSOC);
        return $row ? $row : null;
    }

    public function queryErrorMessage($result)
    {
        return $this->dbHandle->lastErrorCode() . ' ' . $this->dbHandle->lastErrorMsg();
    }

    public function disconnect()
    {
        if ($this->dbHandle) {
            $this->dbHandle->close();
        }
    }

    public function quoteSymbol(Symbol $symbol)
    {
        $result = '';
        foreach ($symbol->names as $name) {
            $result .= $name . '.';
        }
        if ($result) {
            $result = substr($result, 0, -1);
        }

        return $result;
    }

    public function getDialect()
    {
        return Database::DIALECT_SQLITE;
    }

    /**
     * @return Contract
     */
    public function getUtility()
    {
        return new Database\Sqlite\Utility();
    }


}
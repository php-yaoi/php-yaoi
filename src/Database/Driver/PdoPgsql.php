<?php

namespace Yaoi\Database\Driver;

use Yaoi\Database\Driver;
use Yaoi\Database;
use PDO;
use PDOStatement;

class PdoPgsql extends Driver
{
    /** @var  PDO */
    private $connection;

    private function connect()
    {
        $host = $this->dsn->hostname;

        // "host=sheep port=5432 dbname=mary user=lamb password=foo"
        // "host=localhost options='--client_encoding=UTF8'"
        $connectionString = 'pgsql:';
        $connectionString .= 'dbname=' . $this->dsn->path;
        if ($host) {
            $connectionString .= ';host=' . $host;
        }
        if ($this->dsn->port) {
            $connectionString .= ';port=' . $this->dsn->port;
        }
        if ($this->dsn->username) {
            $connectionString .= ';user=' . $this->dsn->username;
        }
        if ($this->dsn->password) {
            $connectionString .= ';password=' . $this->dsn->password;
        }
        $connectionString = trim($connectionString);

        $this->connection = new PDO($connectionString);

        if ($this->dsn->charset) {
            $this->connection->query("SET NAMES '" . $this->dsn->charset . "'");
        }

        if (!$this->connection) {
            throw new Database\Exception('Connection failed', Database\Exception::CONNECTION_ERROR);
        }
        return $this;
    }

    public function query($statement)
    {
        if (null === $this->connection) {
            $this->connect();
        }

        return $this->connection->query($statement);
    }

    public function lastInsertId()
    {
        $res = $this->connection->query("SELECT LASTVAL();");
        if (!$res) {
            throw new Database\Exception(implode(' ', $this->connection->errorInfo()),
                Database\Exception::QUERY_ERROR);
        }
        $row = $res->fetch(PDO::FETCH_ASSOC);
        return $row['lastval'];
    }

    /**
     * @param PDOStatement $result
     * @return int
     */
    public function rowsAffected($result)
    {
        $affectedRows = $result->rowCount();
        return $affectedRows;
    }

    public function escape($value)
    {
        if (null === $this->connection) {
            $this->connect();
        }
        $quoted = $this->connection->quote($value);
        if ($quoted[0] === "'") {
            $quoted = substr($quoted, 1, -1);
        }
        return $quoted;
    }

    /**
     * @param PDOStatement $result
     */
    public function rewind($result)
    {
        if (!empty($result->rewinded)) {
            $result->rewinded = true;
        } else {
            throw new Database\Exception('Can not rewind query result', Database\Exception::REWIND_NOT_SUPPORTED);
        }
    }

    /**
     * @param PDOStatement $result
     * @return array
     */
    public function fetchAssoc($result)
    {
        return $result->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param PDOStatement $result
     * @return string
     */
    public function queryErrorMessage($result)
    {
        return implode(' ', $this->connection->errorInfo());
    }

    public function disconnect()
    {
        if (null !== $this->connection) {
            $this->connection = null;
        }
    }

    public function getDialect()
    {
        return Database::DIALECT_POSTGRESQL;
    }

    /**
     * @return Database\Utility\Contract
     */
    public function getUtility()
    {
        return new Database\Utility\Pgsql();
    }


}
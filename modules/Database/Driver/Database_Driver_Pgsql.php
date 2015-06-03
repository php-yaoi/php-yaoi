<?php

class Database_Driver_Pgsql extends Database_Driver {
    private $connection;

    private function connect() {
        $host = $this->dsn->hostname;

        // "host=sheep port=5432 dbname=mary user=lamb password=foo"
        // "host=localhost options='--client_encoding=UTF8'"
        $connectionString = '';
        if ($host) {
            $connectionString .= ' host=' . $host;
        }
        if ($this->dsn->port) {
            $connectionString .= ' port=' . $this->dsn->port;
        }
        $connectionString .= ' dbname=' . $this->dsn->path;
        if ($this->dsn->username) {
            $connectionString .= ' user=' . $this->dsn->username;
        }
        if ($this->dsn->password) {
            $connectionString .= ' password=' . $this->dsn->password;
        }
        if ($this->dsn->charset) {
            $connectionString .= " options='--client_encoding=" . $this->dsn->charset . "'";
        }
        $connectionString = trim($connectionString);

        $this->connection = pg_connect($connectionString);
        if (!$this->connection) {
            throw new Database_Exception('Connection failed', Database_Exception::CONNECTION_ERROR);
        }
        return $this;
    }

    public function query($statement)
    {
        if (null === $this->connection) {
            $this->connect();
        }

        return pg_query($this->connection, $statement);
    }

    public function lastInsertId()
    {
        $res = pg_query("SELECT LASTVAL();");
        $row = pg_fetch_assoc($res);
        return $row['lastval'];
    }

    public function rowsAffected($result)
    {
        $affectedRows =  pg_affected_rows($result);
        if (!$affectedRows) {
            $affectedRows = pg_num_rows($result);
        }
        return $affectedRows;
    }

    public function escape($value)
    {
        if (null === $this->connection) {
            $this->connect();
        }
        return pg_escape_string($this->connection, $value);
    }

    public function rewind($result)
    {
        pg_result_seek($result, 0);
    }

    public function fetchAssoc($result)
    {
        return pg_fetch_assoc($result);
    }

    public function queryErrorMessage($result)
    {
        return pg_last_error($result);
    }

    public function disconnect()
    {
        if (null !== $this->connection) {
            pg_close($this->connection);
        }
    }

    public function getDialect()
    {
        return Database::DIALECT_POSTGRESQL;
    }

    /**
     * @return Database_Utility_Interface
     */
    public function getUtility()
    {
        return new Database_Utility_Pgsql();
    }


}
<?php

class Database_Driver_Sqlite extends Database_Driver {

    private $dbHandle;

    private function connect() {
        if (null === $this->dbHandle) {
            $this->dbHandle = sqlite_open($this->dsn->path);
        }
    }


    public function query($statement)
    {
        if (null === $this->dbHandle) {
            $this->connect();
        }
        return sqlite_query($this->dbHandle, $statement);
    }

    public function lastInsertId()
    {
        // TODO: Implement lastInsertId() method.
    }

    public function rowsAffected($result)
    {
        // TODO: Implement rowsAffected() method.
    }

    public function escape($value)
    {
        return sqlite_escape_string($value);
    }

    public function rewind($result)
    {
        // TODO: Implement rewind() method.
    }

    public function fetchAssoc($result)
    {
        return sqlite_fetch_array($result, SQLITE_ASSOC);
    }

    public function queryErrorMessage($result)
    {
        // TODO: Implement queryErrorMessage() method.
    }

    public function disconnect()
    {
        // TODO: Implement disconnect() method.
    }
}
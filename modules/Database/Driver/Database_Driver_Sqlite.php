<?php

class Database_Driver_Sqlite implements Database_Server_Mysql  {
    public function query($statement)
    {
        // TODO: Implement query() method.
    }

    public function lastInsertId()
    {
        return sqlite_last_insert_rowid($this->db);
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
        // TODO: Implement fetchAssoc() method.
    }

    public function quote($value)
    {
        // TODO: Implement quote() method.
    }

    public function queryErrorMessage($result)
    {
        // TODO: Implement queryErrorMessage() method.
    }

} 
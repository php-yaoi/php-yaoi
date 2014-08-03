<?php

interface Database_Server_Generic {
    public function __construct(Database_Dsn $dsn);
    public function query($statement);
    public function lastInsertId();
    public function rowsAffected($result);
    public function escape($value);
    public function rewind($result);
    public function fetchAssoc($result);
    public function quote($value);
    public function queryErrorMessage($result);
}
<?php

interface Database_Server_Generic extends Database_Quoter {
    public function __construct(Database_Dsn $dsn);
    public function query($statement);
    public function lastInsertId();
    public function rowsAffected($result);
    public function escape($value);
    public function rewind($result);
    public function fetchAssoc($result);
    public function queryErrorMessage($result);
    public function getTableDefinition($tableName);
    public function disconnect();
    public function getLanguage();
}
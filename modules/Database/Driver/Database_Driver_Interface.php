<?php

interface Database_Driver_Interface extends Database_Quoter {
    public function __construct(Database_Dsn $dsn);
    public function query($statement);
    public function lastInsertId();
    public function rowsAffected($result);
    public function escape($value);
    public function rewind($result);
    public function fetchAssoc($result);
    public function queryErrorMessage($result);
    public function disconnect();
    public function getDialect();

    /**
     * @return Database_Utility_Interface
     */
    public function getUtility();
}
<?php

interface Database_Interface extends Mock_Able, Database_Quoter {
    /**
     * @param null $statement
     * @param null $binds
     * @return Database_Query
     */
    public function query($statement = null, $binds = null);

    /**
     * @param Log $log
     * @return $this
     */
    public function log(Log $log = null);

    /**
     * @param $statement
     * @param null $binds
     * @return Sql_Expression
     */
    public function expr($statement, $binds = null);

    /**
     * @param null $from
     * @return Sql_SelectInterface
     */
    public function select($from = null);

    /**
     * @param null $from
     * @return Sql_DeleteInterface
     */
    public function delete($from = null);

    /**
     * @param null $table
     * @return Sql_UpdateInterface
     */
    public function update($table = null);

    /**
     * @param null $table
     * @return Sql_InsertInterface
     */
    public function insert($table = null);


    /**
     * @return Sql_Statement
     */
    public function statement();

    /**
     * @param $value
     * @return string
     */
    public function quote($value);

    /**
     * @return integer
     */
    public function lastInsertId();

    /**
     * @return $this
     */
    public function disconnect();

    /**
     * @return array
     */
    public function getColumns($tableName);
}
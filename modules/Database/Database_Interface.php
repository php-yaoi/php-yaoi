<?php

interface Database_Interface extends Mock_Able {
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
     * @return Sql_Select
     */
    public function select($from = null);

    /**
     * @param $value
     * @return string
     */
    public function quote($value);

    /**
     * @return integer
     */
    public function lastInsertId();
}
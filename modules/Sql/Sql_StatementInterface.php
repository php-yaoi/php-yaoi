<?php

interface Sql_StatementInterface {
    /**
     * @param Database_Quoter $quoter
     * @return string
     */
    public function build(Database_Quoter $quoter);

    /**
     * @param Database_Interface $client
     * @return Database_Query
     */
    public function query(Database_Interface $client = null);

    /**
     * @param null $table
     * @return Sql_DeleteInterface
     */
    public function delete($table = null);

    /**
     * @param $table
     * @return Sql_InsertInterface
     */
    public function insert($table);

    /**
     * @param $table
     * @return Sql_UpdateInterface
     */
    public function update($table);

    /**
     * @param $expression
     * @param null $binds
     * @return Sql_SelectInterface
     */
    public function select($expression = null, $binds = null);

} 
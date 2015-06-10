<?php

use Yaoi\Database\Contract;
use Yaoi\Database\Quoter;
use Yaoi\Database\Query;

interface Sql_StatementInterface {
    /**
     * @param Quoter $quoter
     * @return string
     */
    public function build(Quoter $quoter);

    /**
     * @param Contract $client
     * @return Query
     */
    public function query(Contract $client = null);

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
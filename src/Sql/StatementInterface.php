<?php

namespace Yaoi\Sql;

use Yaoi\Database\Contract;
use Yaoi\String\Quoter;
use Yaoi\Database\Query;


/**
 * Interface StatementInterface
 * @package Yaoi\Sql
 *
 * @method SelectInterface select($expression, ...$binds) // TODO rename to columns
 */
interface StatementInterface
{
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
     * @return DeleteInterface
     */
    public function delete($table = null);

    /**
     * @param $table
     * @return InsertInterface
     */
    public function insert($table = null);

    /**
     * @param $table
     * @return UpdateInterface
     */
    public function update($table = null);

}
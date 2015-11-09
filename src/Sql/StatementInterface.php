<?php

namespace Yaoi\Sql;

use Yaoi\Database\Contract;
use Yaoi\String\Quoter;
use Yaoi\Database\Query;

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

    /**
     * @param $expression
     * @param null $binds
     * @param mixed $binds, ... unlimited OPTIONAL number of additional binds
     * @return SelectInterface
     */
    public function select($expression = null, $binds = null);

}
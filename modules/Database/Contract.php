<?php

namespace Yaoi\Database;

use Yaoi\Database\Query;
use Yaoi\Database\Quoter;
use Yaoi\Log;
use Mock_Able;
use Sql_DeleteInterface;
use Sql_Expression;
use Sql_InsertInterface;
use Sql_SelectInterface;
use Sql_Statement;
use Sql_UpdateInterface;
use Yaoi\Database\Definition\Table;

interface Contract extends Mock_Able, Quoter
{
    /**
     * @param null $statement
     * @param null $binds
     * @return Query
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
     * @return Table
     */
    public function getTableDefinition($tableName);
}
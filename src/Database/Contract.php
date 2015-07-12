<?php

namespace Yaoi\Database;

use Yaoi\Log;
use Yaoi\Mock\Able;
use Yaoi\Sql\DeleteInterface;
use Yaoi\Sql\Expression;
use Yaoi\Sql\InsertInterface;
use Yaoi\Sql\SelectInterface;
use Yaoi\Sql\Statement;
use Yaoi\Sql\UpdateInterface;
use Yaoi\Database\Definition\Table;

interface Contract extends Able, Quoter
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
     * @return Expression
     */
    public function expr($statement, $binds = null);

    /**
     * @param null $from
     * @return SelectInterface
     */
    public function select($from = null);

    /**
     * @param null $from
     * @return DeleteInterface
     */
    public function delete($from = null);

    /**
     * @param null $table
     * @return UpdateInterface
     */
    public function update($table = null);

    /**
     * @param null $table
     * @return InsertInterface
     */
    public function insert($table = null);


    /**
     * @return Statement
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

    /**
     * @return Utility
     */
    public function getUtility();
}
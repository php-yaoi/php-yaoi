<?php

interface Sql_SelectInterface extends Sql_StatementInterface
{

    /**
     * @param $expression
     * @param null $binds
     * @return $this
     */
    public function from($expression, $binds = null);

    /**
     * @param $expression
     * @param null $binds
     * @return $this
     */
    public function innerJoin($expression, $binds = null);

    /**
     * @param $expression
     * @param null $binds
     * @return $this
     */
    public function leftJoin($expression, $binds = null);

    /**
     * @param $expression
     * @param null $binds
     * @return $this
     */
    public function rightJoin($expression, $binds = null);

    /**
     * @param $expression
     * @param null $binds
     * @return $this
     */
    public function where($expression, $binds = null);

    /**
     * @param $expression
     * @param null $binds
     * @return $this
     */
    public function groupBy($expression, $binds = null);

    /**
     * @param $expression
     * @param null $binds
     * @return $this
     */
    public function having($expression, $binds = null);

    /**
     * @param $expression
     * @param null $binds
     * @return $this
     */
    public function order($expression, $binds = null);

    /**
     * @param $limit
     * @param null $offset
     * @return $this
     */
    public function limit($limit, $offset = null);

    /**
     * @param $offset
     * @return $this
     */
    public function offset($offset);

    /**
     * @param $expression
     * @param null $binds
     * @return $this
     */
    public function union($expression, $binds = null);

    /**
     * @param $expression
     * @param null $binds
     * @return $this
     */
    public function unionAll($expression, $binds = null);
}


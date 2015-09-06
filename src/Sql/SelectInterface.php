<?php

namespace Yaoi\Sql;
use Yaoi\String\Quoter;

/**
 * Interface SelectInterface
 * @package Yaoi\Sql
 * @method SelectInterface from($expression, ...$binds)
 * @method SelectInterface innerJoin($expression, ...$binds)
 * @method SelectInterface leftJoin($expression, ...$binds)
 * @method SelectInterface rightJoin($expression, ...$binds)
 * @method SelectInterface where($expression, ...$binds)
 * @method SelectInterface groupBy($expression, ...$binds)
 * @method SelectInterface having($expression, ...$binds)
 * @method SelectInterface order($expression, ...$binds)
 * @method SelectInterface limit($limit, $offset)
 * @method SelectInterface offset($offset)
 * @method SelectInterface union($expression, ...$binds)
 * @method SelectInterface unionAll($expression, ...$binds)
 */
interface SelectInterface extends StatementInterface
{
    /**
     * @param null $resultClass
     * @return SelectInterface
     */
    public function bindResultClass($resultClass = null);

    /**
     * @param Quoter|null $quoter
     * @return string
     */
    public function build(Quoter $quoter = null);
}


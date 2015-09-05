<?php

namespace Yaoi\Sql;

/**
 * Interface UpdateInterface
 * @package Yaoi\Sql
 * @method SelectInterface innerJoin($expression, ...$binds)
 * @method SelectInterface leftJoin($expression, ...$binds)
 * @method SelectInterface rightJoin($expression, ...$binds)
 * @method SelectInterface where($expression, ...$binds)
 * @method SelectInterface order($expression, ...$binds)
 * @method SelectInterface set($expression, ...$binds)
 * @method SelectInterface limit($limit, $offset)
 * @method SelectInterface offset($offset)
 */
interface UpdateInterface extends StatementInterface
{
}
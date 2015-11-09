<?php

namespace Yaoi\Sql;

/**
 * Interface UpdateInterface
 * @package Yaoi\Sql
 * @method UpdateInterface innerJoin($expression, ...$binds)
 * @method UpdateInterface leftJoin($expression, ...$binds)
 * @method UpdateInterface rightJoin($expression, ...$binds)
 * @method UpdateInterface where($expression, ...$binds)
 * @method UpdateInterface order($expression, ...$binds)
 * @method UpdateInterface set($expression, ...$binds)
 * @method UpdateInterface limit($limit, $offset = null)
 * @method UpdateInterface offset($offset)
 */
interface UpdateInterface extends StatementInterface
{
}
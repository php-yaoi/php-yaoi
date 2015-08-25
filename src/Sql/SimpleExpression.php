<?php

namespace Yaoi\Sql;

use Closure;
use Yaoi\BaseClass;
use Yaoi\Database;
use Yaoi\String\Quoter;
use Yaoi\Debug;

/**
 * Class Expression
 * @package Yaoi\Sql
 * @todo Utilize String\Formatter
 */
class SimpleExpression extends Expression implements \Yaoi\IsEmpty
{

    /**
     * @param $arguments
     * @return SimpleExpression
     * @throws \Yaoi\Sql\Exception
     */
    public static function createFromFuncArguments($arguments)
    {
        if (empty($arguments[0])) {
            throw new \Yaoi\Sql\Exception('Literal statement or Sql_Expression required as first argument',
                \Yaoi\Sql\Exception::STATEMENT_REQUIRED);
        }
        if ($arguments[0] instanceof Expression) {
            return $arguments[0];
        }
        if ($arguments[0] instanceof Symbol) {
            return new self('?', $arguments[0]);
        }
        if ($arguments[0] instanceof Database\Definition\Column) {
            return new self('?', $arguments[0]);
        }
        if ($arguments[0] instanceof Database\Definition\Table) {
            return new self('?', $arguments[0]);
        }
        if ($arguments[0] instanceof Closure) {
            $expression = $arguments[0]();
            if (!$expression instanceof Expression) {
                throw new \Yaoi\Sql\Exception('Closure should return ' . get_called_class(),
                    \Yaoi\Sql\Exception::CLOSURE_MISTYPE);
            }
            return $expression;
        }
        if ($arguments[0] instanceof \stdClass) {
            $columns = $arguments[0];
            $arguments = array(':columns', array('columns' => array()));
            foreach ((array)$columns as $column) {
                if ($column instanceof Database\Definition\Column) {
                    $arguments[1]['columns'][] = $column;
                }
            }
        }

        $expression = new self;
        $expression->setFromFuncArguments($arguments);
        return $expression;
    }

    private function setFromFuncArguments($arguments)
    {
        $this->statement = $arguments[0];

        $count = count($arguments);
        if ($count > 2) {
            array_shift($arguments);
            $this->binds = $arguments;
        } elseif (array_key_exists(1, $arguments)) {
            if (is_array($arguments[1])) {
                $this->binds = $arguments[1];
            } else {
                $this->binds = array($arguments[1]);
            }
        }
    }

    public function __construct($statement = null, $binds = null)
    {
        if (null !== $statement) {
            $this->setFromFuncArguments(func_get_args());
        }
    }

}
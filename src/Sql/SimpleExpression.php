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
     * @param $operation
     * @return SimpleExpression
     * @throws \Yaoi\Sql\Exception
     * @todo simplify a lot, please
     */
    public static function createFromFuncArguments($arguments, $operation = ' ')
    {
        if (empty($arguments)) {
            return new static();
        }

        if (count($arguments) === 2 && is_string($arguments[0] && is_array($arguments[1]))) {
            $expr = new self;
            $expr->statement = $arguments[0];
            $expr->binds = $arguments[1];
            return $expr;
        }


        foreach ($arguments as &$argument) {
            if ($argument instanceof Closure) {
                $argument = $argument();
            }

            if ($argument instanceof Database\Definition\Columns) {
                $options = $argument->getArray();
                $argument = new self();
                $argument->statement = substr(str_repeat($operation . '?', count($options)), strlen($operation));
                $argument->binds = $options;
            }
        }

        $expr = new self;

        if (is_string($arguments[0])) {
            $expr->statement = array_shift($arguments);
            $expr->binds = $arguments;
        } else {
            $expr->statement = substr(str_repeat($operation . '?', count($arguments)), strlen($operation));
            $expr->binds = $arguments;
        }

        return $expr;
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
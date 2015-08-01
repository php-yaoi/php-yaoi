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
class Expression extends BaseClass implements \Yaoi\IsEmpty
{
    /**
     * @var Database
     */
    protected $database;

    public function bindDatabase(Database\Contract $client = null)
    {
        $this->database = $client;
        return $this;
    }

    public function __toString()
    {
        if (!$this->database) {
            return '/* ERROR: Unknown database */';
        }

        try {
            $res = $this->build($this->database->getDriver());
            return $res;
        } catch (\Exception $e) {
            return '/* ERROR: ' . $e->getMessage() . ' */';
        }
    }


    /**
     * @param $arguments
     * @return Expression
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
        if ($arguments[0] instanceof Closure) {
            $expression = $arguments[0]();
            if (!$expression instanceof Expression) {
                throw new \Yaoi\Sql\Exception('Closure should return ' . get_called_class(),
                    \Yaoi\Sql\Exception::CLOSURE_MISTYPE);
            }
            return $expression;
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

    private $as;
    private $statement;
    private $binds;
    private $queue = array();


    public function asExpr($as)
    {
        $this->as = $as;
        return $this;
    }


    const OP_AND = ' AND ';

    public function andExpr($expression)
    {
        $this->queue [] = array(self::OP_AND, Expression::createFromFuncArguments(func_get_args()));
        return $this;
    }


    const OP_OR = ' OR ';

    public function orExpr($expression)
    {
        $this->queue [] = array(self::OP_OR, Expression::createFromFuncArguments(func_get_args()));
        return $this;
    }


    const OP_XOR = ' XOR ';

    public function xorExpr($expression)
    {
        $this->queue [] = array(self::OP_XOR, Expression::createFromFuncArguments(func_get_args()));
        return $this;
    }

    const OP_COMMA = ', ';

    public function commaExpr($expression)
    {
        $this->queue [] = array(self::OP_COMMA, Expression::createFromFuncArguments(func_get_args()));
        return $this;
    }

    const OP_APPEND = '';

    public function appendExpr($expression)
    {
        $this->queue [] = array(self::OP_APPEND, Expression::createFromFuncArguments(func_get_args()));
        return $this;
    }

    const OP_UNION = ' UNION ';

    public function unionExpr($expression)
    {
        $this->queue [] = array(self::OP_UNION, Expression::createFromFuncArguments(func_get_args()));
        return $this;
    }

    const OP_UNION_ALL = ' UNION ALL ';

    public function unionAllExpr($expression)
    {
        $this->queue [] = array(self::OP_UNION_ALL, Expression::createFromFuncArguments(func_get_args()));
        return $this;
    }


    public function prependExpr($expression)
    {
        array_unshift(
            $this->queue,
            array(
                self::OP_APPEND,
                Expression::createFromFuncArguments(func_get_args())
            )
        );
        return $this;
    }


    public function build(Quoter $quoter = null)
    {
        if ($this->isEmpty()) {
            return '';
        }

        if (null === $quoter) {
            if ($this->database) {
                $quoter = $this->database->getDriver();
            }
        }


        if ($this->binds) {
            if ($quoter === null) {
                throw new Exception('Missing quoter', Exception::MISSING_QUOTER);
            }

            $statement = $this->statement;

            $replace = array();
            $unnamed = true;

            // check binds array type
            $i = 0;
            foreach ($this->binds as $key => $value) {
                if ($unnamed && $key !== $i++) {
                    $unnamed = false;
                    break;
                }
            }

            if ($unnamed) {
                $pos = 0;
                foreach ($this->binds as $value) {
                    $pos = strpos($statement, '?', $pos);
                    if ($pos !== false) {
                        $value = $quoter->quote($value);
                        $statement = substr_replace($statement, $value, $pos, 1);
                        $pos += strlen($value);
                    } else {
                        throw new \Yaoi\Database\Exception('Placeholder \'?\' not found ("' . $statement . '"), '
                            . Debug::varBrief($this->binds), \Yaoi\Database\Exception::PLACEHOLDER_NOT_FOUND);
                    }
                }

                if (strpos($statement, '?', $pos) !== false) {
                    throw new \Yaoi\Database\Exception('Redundant placeholder: "' . $statement . '"',
                        \Yaoi\Database\Exception::PLACEHOLDER_REDUNDANT);
                }

                $result = $statement;
            } else {
                foreach ($this->binds as $key => $value) {
                    $replace [':' . $key] = $quoter->quote($value);
                }
                $result = strtr($statement, $replace);
            }
        } else {
            $result = $this->statement;
        }

        foreach ($this->queue as $item) {
            /**
             * @var Expression $expression
             */
            $expression = $item[1];

            if (!$expression->isEmpty()) {
                $result .= $item[0] . $expression->build($quoter);
            }
        }

        return $this->as ? '(' . $result . ') AS ' . $this->as : $result;
    }


    protected $disabled = false;

    public function disable()
    {
        $this->disabled = true;
        return $this;
    }

    public function enable()
    {
        $this->disabled = false;
        return $this;
    }


    public function isEmpty()
    {
        if ($this->disabled) {
            return true;
        }
        if ($this->queue || $this->statement) {
            return false;
        }
        return true;
    }
}
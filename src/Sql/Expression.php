<?php

namespace Yaoi\Sql;


use Yaoi\BaseClass;
use Yaoi\Database;
use Yaoi\Debug;
use Yaoi\DependencyRepository;
use Yaoi\String\Quoter;

abstract class Expression extends BaseClass
{
    private $databaseRefId;

    /**
     * @return Database|null
     */
    protected function database() {
         return DependencyRepository::get($this->databaseRefId);
    }

    public function bindDatabase(Database\Contract $client = null)
    {
        $this->databaseRefId = DependencyRepository::add($client);
        return $this;
    }

    public function __toString()
    {
        if (!$this->database()) {
            return '/* ERROR: Unknown database */';
        }

        try {
            $res = $this->build($this->database()->getDriver());
            return $res;
        } catch (\Exception $e) {
            return '/* ERROR: ' . $e->getMessage() . ' */';
        }
    }

    private $as;
    protected $statement;
    protected $binds;
    protected $queue = array();
    protected $operand;

    public function asExpr($as)
    {
        $this->as = $as;
        return $this;
    }


    const OP_AND = ' AND ';

    public function andExpr($expression)
    {
        $this->queue [] = array(self::OP_AND, SimpleExpression::createFromFuncArguments(func_get_args()));
        return $this;
    }

    protected function addExpr($operand, Expression $expression)
    {
        $this->queue []= array($operand, $expression);
    }

    const OP_OR = ' OR ';

    public function orExpr($expression)
    {
        $this->queue [] = array(self::OP_OR, SimpleExpression::createFromFuncArguments(func_get_args()));
        return $this;
    }


    const OP_XOR = ' XOR ';

    public function xorExpr($expression)
    {
        $this->queue [] = array(self::OP_XOR, SimpleExpression::createFromFuncArguments(func_get_args()));
        return $this;
    }

    const OP_COMMA = ', ';
    private $opComma = self::OP_COMMA;
    public function setOpComma($separator) {
        $this->opComma = $separator;
        return $this;
    }

    public function commaExpr($expression)
    {
        $this->queue [] = array($this->opComma, SimpleExpression::createFromFuncArguments(func_get_args()));
        return $this;
    }

    const OP_APPEND = '';

    public function appendExpr($expression)
    {
        $this->queue [] = array(self::OP_APPEND, SimpleExpression::createFromFuncArguments(func_get_args()));
        return $this;
    }

    public function prependExpr($expression)
    {
        array_unshift(
            $this->queue,
            array(
                self::OP_APPEND,
                SimpleExpression::createFromFuncArguments(func_get_args())
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
            if ($this->database()) {
                $quoter = $this->database()->getDriver();
            }
        }


        $result = '';
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
        } elseif ($this->statement) {
            $result = $this->statement;
        }


        foreach ($this->queue as $item) {
            /**
             * @var SimpleExpression $expression
             */
            $expression = $item[1];

            if (!$expression->isEmpty()) {
                $result .= ($result ? $item[0] : '') . $expression->build($quoter);
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
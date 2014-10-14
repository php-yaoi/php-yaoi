<?php

class Sql_Expression extends Base_Class implements Is_Empty {



    /**
     * @param $arguments
     * @return Sql_Expression
     * @throws Sql_Exception
     */
    public static function createFromFuncArguments($arguments) {
        if (empty($arguments[0])) {
            throw new Sql_Exception('Literal statement or Sql_Expression required as first argument',
                Sql_Exception::STATEMENT_REQUIRED);
        }
        if ($arguments[0] instanceof Sql_Expression) {
            return $arguments[0];
        }
        if ($arguments[0] instanceof Closure) {
            $expression = $arguments[0]();
            if (!$expression instanceof Sql_Expression) {
                throw new Sql_Exception('Closure should return Sql_Expression',
                    Sql_Exception::CLOSURE_MISTYPE);
            }
            return $expression;
        }

        $expression = new self;
        $expression->setFromFuncArguments($arguments);
        return $expression;
    }

    private function setFromFuncArguments($arguments) {
        $this->statement = $arguments[0];

        $count = count($arguments);
        if ($count > 2) {
            array_shift($arguments);
            $this->binds = $arguments;
        }
        elseif (isset($arguments[1])) {
            if (is_array($arguments[1])) {
                $this->binds = $arguments[1];
            }
            else {
                $this->binds = array($arguments[1]);
            }
        }
    }

    public function __construct($statement = null, $binds = null) {
        if (null !== $statement) {
            $this->setFromFuncArguments(func_get_args());
        }
    }

    private $as;
    private $statement;
    private $binds;
    private $queue = array();


    public function asExpr($as) {
        $this->as = $as;
        return $this;
    }


    const OP_AND = ' AND ';
    public function andExpr($expression) {
        $this->queue []= array(self::OP_AND, Sql_Expression::createFromFuncArguments(func_get_args()));
        return $this;
    }


    const OP_OR = ' OR ';
    public function orExpr($expression) {
        $this->queue []= array(self::OP_OR, Sql_Expression::createFromFuncArguments(func_get_args()));
        return $this;
    }


    const OP_XOR = ' XOR ';
    public function xorExpr($expression) {
        $this->queue []= array(self::OP_XOR, Sql_Expression::createFromFuncArguments(func_get_args()));
        return $this;
    }

    const OP_COMMA = ', ';
    public function commaExpr($expression) {
        $this->queue []= array(self::OP_COMMA, Sql_Expression::createFromFuncArguments(func_get_args()));
        return $this;
    }

    const OP_APPEND = ' ';
    public function appendExpr($expression) {
        $this->queue []= array(self::OP_APPEND, Sql_Expression::createFromFuncArguments(func_get_args()));
        return $this;
    }

    public function prependExpr($expression) {
        array_unshift(
            $this->queue,
            array(
                self::OP_APPEND,
                Sql_Expression::createFromFuncArguments(func_get_args())
            )
        );
        return $this;
    }




    public function build(Database_Quoter $quoter = null) {
        if ($this->isEmpty()) {
            return '';
        }

        if ($this->binds && $quoter !== null) {
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
                        throw new Database_Exception('Placeholder \'?\' not found', Database_Exception::PLACEHOLDER_NOT_FOUND);
                    }
                }

                if (strpos($statement, '?', $pos) !== false) {
                    throw new Database_Exception('Redundant placeholder: "' . $statement . '"',
                        Database_Exception::PLACEHOLDER_REDUNDANT);
                }

                $result = $statement;
            } else {
                foreach ($this->binds as $key => $value) {
                    $replace [':' . $key] = $quoter->quote($value);
                }
                $result = strtr($statement, $replace);
            }
        }
        else {
            $result = $this->statement;
        }

        foreach ($this->queue as $item) {
            /**
             * @var Sql_Expression $expression
             */
            $expression = $item[1];

            if (!$expression->isEmpty()) {
                $result .= $item[0] . $expression->build($quoter);
            }
        }

        return $this->as ? '(' . $result . ') AS ' . $this->as  : $result;
    }


    private $disabled = false;
    public function disable() {
        $this->disabled = true;
        return $this;
    }

    public function enable() {
        $this->disabled = false;
        return $this;
    }


    public function isEmpty()
    {
        return $this->disabled || null === $this->statement || '' === $this->statement;
    }
}
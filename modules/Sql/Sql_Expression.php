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
                throw new Sql_Exception('Closure argument should return Sql_Expression',
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

    /**
     * @param Database|Database_Interface $client
     * @return mixed|string
     * @throws Database_Exception
     */
    public function build(Database $client) {
        if ($this->isEmpty()) {
            return '';
        }

        if ($this->binds) {
            $result = $client->buildString($this->statement, $this->binds);
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
                $result .= $item[0] . $expression->build($client);
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
        return $this->disabled;
    }
}
<?php

class Sql_Statement extends Base_Class implements Is_Empty {
    /*
    select
    from
    joins
    where
    group by
    having
    order by
    limit
    */

    public static function createFromFuncArguments($arguments) {
        if (empty($arguments[0])) {
            throw new Sql_Exception('Literal statement or Sql_Expression required as first argument',
                Sql_Exception::STATEMENT_REQUIRED);
        }
        if ($arguments[0] instanceof Sql_Statement) {
            return $arguments[0];
        }
        if ($arguments[0] instanceof Closure) {
            $statement = $arguments[0]();
            if (!$statement instanceof Sql_Statement) {
                throw new Sql_Exception('Closure argument should return Sql_Expression',
                    Sql_Exception::CLOSURE_MISTYPE);
            }
            return $statement;
        }

        $statement = new self;
        $statement->setFromFuncArguments($arguments);
        return $statement;
    }

    private $statement;
    private $binds;

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



    public function isEmpty()
    {
        return $this->disabled;
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

}
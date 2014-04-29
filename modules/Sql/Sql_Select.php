<?php

class Sql_Select extends Sql_Statement {
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


    public function __construct($from = null) {
        if (null !== $from) {
            $this->from($from);
        }
    }

    /**
     * @var Sql_Expression[]
     */
    protected $columns = array();
    public function columns($expression, $binds = null) {
        $this->columns []= Sql_Expression::createFromFuncArguments(func_get_args());
        return $this;
    }

    protected function buildColumns(Database $client) {
        $columns = '';
        if ($this->columns) {
            foreach ($this->columns as $column) {
                if (!$column->isEmpty()) {
                    $columns .= $column->build($client) . ', ';
                }
            }
            if ($columns) {
                $columns = substr($columns, 0, -2);
            }
        }
        else {
            $columns = '*';
        }

        if (!$columns) {
            throw new Sql_Exception('Missing columns in SELECT statement', Sql_Exception::MISSING_COLUMNS);
        }
        return ' ' . $columns;
    }


    /**
     * @var Sql_Expression[]
     */
    protected $from = array();
    public function from($expression, $binds = null) {
        $this->from []= Sql_Expression::createFromFuncArguments(func_get_args());
        return $this;
    }

    protected function buildFrom(Database $client) {
        $from = '';
        if ($this->from) {
            foreach ($this->from as $expression) {
                $from .= $expression->build($client);
                $from .= ', ';
            }

            if ($from) {
                $from = ' FROM ' . substr($from, 0, -2);
            }
        }

        return $from;
    }



    const JOIN_LEFT = 'LEFT';
    const JOIN_RIGHT = 'RIGHT';
    const JOIN_INNER = 'INNER';
    protected $join = array();
    public function leftJoin($expression, $binds) {
        $this->join []= array(self::JOIN_LEFT, Sql_Expression::createFromFuncArguments(func_get_args()));
        return $this;
    }
    public function rightJoin($fromExpression, $as = null, $on = null) {
        $this->join []= array($fromExpression, $as, $on, self::JOIN_RIGHT);
        return $this;
    }
    public function innerJoin($fromExpression, $as = null, $on = null) {
        $this->join []= array($fromExpression, $as, $on, self::JOIN_INNER);
        return $this;
    }

    protected function buildJoin(Database $client) {
        foreach ($this->join as $item) {

        }
        return '';
    }




    /**
     * @var Sql_Expression
     */
    protected $where;
    public function where($expression, $binds = null) {
        if (null === $expression) {
            return $this;
        }

        if (null === $this->where) {
            $this->where = Sql_Expression::createFromFuncArguments(func_get_args());
        }
        else {
            $this->where->andExpr(Sql_Expression::createFromFuncArguments(func_get_args()));
        }
        return $this;
    }

    protected function buildWhere(Database $client) {
        $where = '';

        if ($this->where && !$this->where->isEmpty()) {
            $where = ' WHERE ' . $this->where->build($client);
        }

        return $where;
    }


    public function build(Database $client = null) {
        $q = "SELECT";

        $q .= $this->buildColumns($client);
        $q .= $this->buildFrom($client);
        //$q .= $this->buildJoin($client);
        $q .= $this->buildWhere($client);


        return $q;
    }
}


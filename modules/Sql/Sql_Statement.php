<?php

class Sql_Statement extends Sql_Expression {

    /**
     * @var Database
     */
    protected $database;
    public function bindDatabase(Database $client = null) {
        $this->database = $client;
        return $this;
    }

    public function query() {
        return $this->database->query($this);
    }

    public function __toString() {
        return $this->build($this->database);
    }



    const CMD_SELECT = 'SELECT';
    const CMD_DELETE = 'DELETE';
    const CMD_INSERT = 'INSERT';
    const CMD_UPDATE = 'UPDATE';
    private $command;

    protected $set = array();
    public function update($set, $binds) {
        // TODO implement
        // UPDATE t1 LEFT JOIN t2 ON t1.e = t2.e SET t1.c = t2.cc WHERE t1.ff = 45
        // UPDATE t1 SET dd = 1 WHERE ddd = 2
        $this->command = self::CMD_UPDATE;
        $this->set []= Sql_Expression::createFromFuncArguments(func_get_args());
        return $this;
    }

    public function insert() {
        // TODO insert is totally different, no join, no where
        $this->command = self::CMD_INSERT;
        return $this;
    }

    public function delete() {
        // TODO implement
        $this->command = self::CMD_DELETE;
        return $this;
    }




    /**
     * @var Sql_Expression[]
     */
    protected $select = array();
    public function select($expression, $binds = null) {
        $this->command = self::CMD_SELECT;
        $this->select []= Sql_Expression::createFromFuncArguments(func_get_args());
        return $this;
    }

    protected function buildSelect(Database $client) {
        $columns = '';
        if ($this->select) {
            foreach ($this->select as $column) {
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


    /**
     * @var Sql_Expression
     */
    protected $order;
    public function order($expression, $binds = null) {
        if (null === $expression) {
            return $this;
        }

        if (null === $this->order) {
            $this->order = Sql_Expression::createFromFuncArguments(func_get_args());
        }
        else {
            $this->order->commaExpr(Sql_Expression::createFromFuncArguments(func_get_args()));
        }
        return $this;
    }

    protected function buildOrder(Database $client) {
        $order = '';

        if ($this->order && !$this->order->isEmpty()) {
            $order = ' ORDER BY ' . $this->order->build($client);
        }

        return $order;
    }


    private $limit;
    private $offset;
    public function limit($limit, $offset = null) {
        $this->limit = $limit;
        if (null !== $offset) {
            $this->offset = $offset;
        }
        return $this;
    }
    public function offset($offset) {
        $this->offset = $offset;
        return $this;
    }
    protected  function buildLimit() {
        if ($this->limit) {
            return ' LIMIT ' . $this->limit . ($this->offset ? ' OFFSET ' . $this->offset : '');
        }
        else {
            return '';
        }
    }

}
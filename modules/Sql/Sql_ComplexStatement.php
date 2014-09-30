<?php
/*
 *
 * create table test1 select * from sources
###
create table test2 select * from actual order by rand() limit 50
###
update test1,test2 set test1.name = concat(test1.name, test2.pressure), test2.cloudiness = test1.class where test1.id=test2.sourceId
###
update test2 left join test1 on test1.id = test2.sourceId  SET test1.class = test2.temperature, test2.humidity = test1.name
###
delete from test2 limit 4
###
delete test2,test1 from test1 left join test2 on test1.id = test2.sourceId where test2.temperature<0
###
select * from test1, test2 where test1.id=test2.sourceId limit 500
###
drop table test1
###
drop table test2
 */

class Sql_ComplexStatement extends Sql_Expression
{

    /**
     * @var Sql_Expression[]
     */
    protected $from = array();

    public function from($expression, $binds = null)
    {
        $this->from [] = Sql_Expression::createFromFuncArguments(func_get_args());
        return $this;
    }

    protected function buildFrom(Database $client)
    {
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
    /**
     * @var Sql_Expression[]
     */
    protected $join = array();

    public function leftJoin($expression, $binds = null)
    {
        $this->join [] = array(self::JOIN_LEFT, Sql_Expression::createFromFuncArguments(func_get_args()));
        return $this;
    }

    public function rightJoin($expression, $binds = null)
    {
        $this->join [] = array(self::JOIN_RIGHT, Sql_Expression::createFromFuncArguments(func_get_args()));
        return $this;
    }

    public function innerJoin($expression, $binds = null)
    {
        $this->join [] = array(self::JOIN_INNER, Sql_Expression::createFromFuncArguments(func_get_args()));
        return $this;
    }

    protected function buildJoin(Database $client)
    {
        $join = '';
        foreach ($this->join as $item) {
            $direction = $item[0];
            /**
             * @var Sql_Expression $expression
             */
            $expression = $item[1];
            if ($expression && !$expression->isEmpty()) {
                $join .= ' ' . $direction . ' JOIN ' . $expression->build($client);
            }

        }
        return $join;
    }


    /**
     * @var Sql_Expression
     */
    protected $where;

    public function where($expression, $binds = null)
    {
        if (null === $expression) {
            return $this;
        }

        if (null === $this->where) {
            $this->where = Sql_Expression::createFromFuncArguments(func_get_args());
        } else {
            $this->where->andExpr(Sql_Expression::createFromFuncArguments(func_get_args()));
        }
        return $this;
    }

    protected function buildWhere(Database $client)
    {
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

    public function order($expression, $binds = null)
    {
        if (null === $expression) {
            return $this;
        }

        if (null === $this->order) {
            $this->order = Sql_Expression::createFromFuncArguments(func_get_args());
        } else {
            $this->order->commaExpr(Sql_Expression::createFromFuncArguments(func_get_args()));
        }
        return $this;
    }

    protected function buildOrder(Database $client)
    {
        $order = '';

        if ($this->order && !$this->order->isEmpty()) {
            $order = ' ORDER BY ' . $this->order->build($client);
        }

        return $order;
    }


    private $limit;
    private $offset;

    public function limit($limit, $offset = null)
    {
        $this->limit = $limit;
        if (null !== $offset) {
            $this->offset = $offset;
        }
        return $this;
    }

    public function offset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    protected function buildLimit()
    {
        if ($this->limit) {
            return ' LIMIT ' . $this->limit . ($this->offset ? ' OFFSET ' . $this->offset : '');
        } else {
            return '';
        }
    }

    /**
     * @var Sql_Expression
     */
    private $groupBy;

    public function groupBy($expression, $binds = null)
    {
        if (null === $expression) {
            return $this;
        }

        if (null === $this->groupBy) {
            $this->groupBy = Sql_Expression::createFromFuncArguments(func_get_args());
        } else {
            $this->groupBy->commaExpr(Sql_Expression::createFromFuncArguments(func_get_args()));
        }

        return $this;
    }

    protected function buildGroupBy(Database $client)
    {
        if ($this->groupBy && !$this->groupBy->isEmpty()) {
            return ' GROUP BY ' . $this->groupBy->build($client);
        } else {
            return '';
        }
    }


    /**
     * @var Sql_Expression
     */
    private $having;

    public function having($expression, $binds = null)
    {
        if (null === $expression) {
            return $this;
        }

        if (null === $this->having) {
            $this->having = Sql_Expression::createFromFuncArguments(func_get_args());
        } else {
            $this->having->andExpr(Sql_Expression::createFromFuncArguments(func_get_args()));
        }

        return $this;
    }

    protected function buildHaving(Database $client)
    {
        if ($this->having && !$this->having->isEmpty()) {
            return ' HAVING ' . $this->having->build($client);
        } else {
            return '';
        }
    }

    /**
     * @var Sql_Expression
     */
    private $set;




    public function set($expression, $binds = null)
    {

        // TODO implement
        if (null === $expression) {
            return $this;
        }

        if (is_array($expression)) {
            if (is_string($binds)) {
                $table = '`' . $binds . '`.';
            } else {
                $table = '';
            }

            $e = '';
            $b = array();
            foreach ($expression as $key => $value) {
                $e .= $table . '`' . $key . '` = ?, ';
                $b [] = $value;
            }
            $e = substr($e, 0, -2);
            $expression = new Sql_Expression($e, $b);
        } else {
            $expression = Sql_Expression::createFromFuncArguments(func_get_args());
        }

        if (null === $this->set) {
            $this->set = $expression;
        } else {
            $this->set->commaExpr($expression);
        }

        return $this;
    }

    protected function buildSet(Database $client)
    {
        if ($this->set && !$this->set->isEmpty()) {
            return ' SET ' . $this->set->build($client);
        } else {
            return '';
        }
    }


    private $values;
    public function valuesRow($array) {
        if (null === $this->values) {
            $this->values = array();
        }
        $this->values []= $array;
        return $this;
    }

    public function valuesRows($collection) {
        if (null === $this->values) {
            $this->values = array();
        }
        foreach ($collection as $array) {
            $this->values []= $array;
        }
        return $this;
    }

    protected function buildValues(Database $client) {
        $result = '';
        if ($this->values) {
            $fields = array();
            foreach ($this->values as $row) {
                foreach (array_keys($row) as $field) {
                    $fields[$field] = $field;
                }
            }
            if (!$fields) {
                return $result;
            }
            $result .= '(';
            foreach ($fields as $field) {
                $result .= $client->symbol($field) . ', ';
            }
            $result = substr($result, 0, -2) . ') VALUES ';

            foreach ($this->values as $row) {
                $rowString = '';
                foreach ($fields as $field) {
                    if (array_key_exists($field, $row)) {
                        $value = $row[$field];
                    }
                    else {
                        $value = new Sql_DefaultValue();
                    }

                    $rowString .= $client->quote($value) . ', ';
                }
                $result .= '(' . substr($rowString, 0, -2) . '), ';
            }
            $result = substr($result, 0, -2);
            return $result;
        }
        else {
            return $result;
        }
    }

}
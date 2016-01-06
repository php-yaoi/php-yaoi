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

namespace Yaoi\Sql;
use Yaoi\Database\Entity;
use Yaoi\String\Quoter;

abstract class ComplexStatement extends Expression implements
    SelectInterface,
    InsertInterface,
    UpdateInterface,
    DeleteInterface
{

    const JOIN_LEFT = 'LEFT';
    const JOIN_RIGHT = 'RIGHT';
    const JOIN_INNER = 'INNER';
    /**
     * @var Expression[]
     */
    protected $join = array();

    public function leftJoin($expression, $binds = null)
    {
        $this->join [] = array(self::JOIN_LEFT, SimpleExpression::createFromFuncArguments(func_get_args()));
        return $this;
    }

    public function rightJoin($expression, $binds = null)
    {
        $this->join [] = array(self::JOIN_RIGHT, SimpleExpression::createFromFuncArguments(func_get_args()));
        return $this;
    }

    public function innerJoin($expression, $binds = null)
    {
        $this->join [] = array(self::JOIN_INNER, SimpleExpression::createFromFuncArguments(func_get_args()));
        return $this;
    }

    protected function buildJoin(Quoter $quoter)
    {
        $join = '';
        foreach ($this->join as $item) {
            $direction = $item[0];
            /**
             * @var SimpleExpression $expression
             */
            $expression = $item[1];
            if ($expression && !$expression->isEmpty()) {
                $join .= ' ' . $direction . ' JOIN ' . $expression->build($quoter);
            }

        }
        return $join;
    }


    /**
     * @var SimpleExpression
     */
    protected $where;

    private function initAdd($field, $operand, $arguments)
    {
        if (empty($arguments[0])) {
            return $this;
        }

        /** @var SimpleExpression $property */
        $property = &$this->$field;
        if (null === $property) {
            $property = SimpleExpression::createFromFuncArguments($arguments);
        } else {
            $property->addExpr($operand, SimpleExpression::createFromFuncArguments($arguments));
        }
        return $this;

    }

    /** @var  SimpleExpression */
    protected $tables;
    public function from($expression, $binds = null) {
        return $this->initAdd('tables', self::OP_COMMA, func_get_args());
    }

    public function buildFrom(Quoter $quoter) {
        $from = '';

        if ($this->tables && !$this->tables->isEmpty()) {
            $from = ' FROM ' . $this->tables->build($quoter);
        }

        return $from;
    }


    public function buildTable(Quoter $quoter) {
        $tables = '';

        if ($this->tables && !$this->tables->isEmpty()) {
            $tables = ' ' . $this->tables->build($quoter);
        }

        return $tables;
    }


    public function where($expression, $binds = null)
    {
        return $this->initAdd('where', self::OP_AND, func_get_args());
    }

    protected function buildWhere(Quoter $quoter)
    {
        $where = '';

        if ($this->where && !$this->where->isEmpty()) {
            $where = ' WHERE ' . $this->where->build($quoter);
        }

        return $where;
    }


    /**
     * @var SimpleExpression
     */
    protected $order;

    public function order($expression, $binds = null)
    {
        return $this->initAdd('order', self::OP_COMMA, func_get_args());
    }

    protected function buildOrder(Quoter $quoter)
    {
        $order = '';

        if ($this->order && !$this->order->isEmpty()) {
            $order = ' ORDER BY ' . $this->order->build($quoter);
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
     * @var Expression
     */
    private $groupBy;

    public function groupBy($expression, $binds = null)
    {
        return $this->initAdd('groupBy', self::OP_COMMA, func_get_args());
    }

    protected function buildGroupBy(Quoter $quoter)
    {
        if ($this->groupBy && !$this->groupBy->isEmpty()) {
            return ' GROUP BY ' . $this->groupBy->build($quoter);
        } else {
            return '';
        }
    }


    /**
     * @var Expression
     */
    private $having;

    public function having($expression, $binds = null)
    {
        return $this->initAdd('having', self::OP_AND, func_get_args());
    }

    protected function buildHaving(Quoter $quoter)
    {
        if ($this->having && !$this->having->isEmpty()) {
            return ' HAVING ' . $this->having->build($quoter);
        } else {
            return '';
        }
    }


    /**
     * @var Expression
     */
    private $union;

    const OP_UNION = ' UNION ';
    const OP_UNION_ALL = ' UNION ALL ';

    /**
     * @param $expression
     * @param null $binds
     * @return mixed
     */
    public function union($expression, $binds = null)
    {
        if (null === $this->union) {
            $this->union = new SimpleExpression(' ');
        }
        return $this->initAdd('union', self::OP_UNION, func_get_args());
    }


    /**
     * @param $expression
     * @param null $binds
     * @return mixed
     */
    public function unionAll($expression, $binds = null)
    {
        return $this->initAdd('union', self::OP_UNION_ALL, func_get_args());
    }

    protected function buildUnion(Quoter $quoter)
    {
        if ($this->union && !$this->union->isEmpty()) {
            return substr($this->union->build($quoter), 1);
        } else {
            return '';
        }
    }


    /**
     * @var Expression
     */
    private $set;


    public function set($expression, $binds = null)
    {
        if (null === $expression) {
            return $this;
        }

        if (is_array($expression)) {
            $expressionString = '';
            $bindsArray = array();
            if (is_string($binds)) {
                $table = $binds;
            } else {
                $table = null;
            }

            foreach ($expression as $key => $value) {
                $expressionString .= '? = ?, ';
                $bindsArray [] = new Symbol($table, $key);
                $bindsArray [] = $value;
            }

            $expressionString = substr($expressionString, 0, -2);
            $expression = new SimpleExpression($expressionString, $bindsArray);
        } else {
            $expression = SimpleExpression::createFromFuncArguments(func_get_args());
        }

        if (null === $this->set) {
            $this->set = $expression;
        } else {
            $this->set->commaExpr($expression);
        }

        return $this;
    }

    protected function buildSet(Quoter $quoter)
    {
        if ($this->set && !$this->set->isEmpty()) {
            return ' SET ' . $this->set->build($quoter);
        } else {
            return '';
        }
    }


    private $values;

    public function valuesRow($array)
    {
        return $this->valuesRows(array($array));
    }

    public function valuesRows($collection)
    {
        if (null === $this->values) {
            $this->values = array();
        }
        foreach ($collection as $item) {
            if ($item instanceof Entity) {
                $this->values [] = $item->toArray();
            }
            else {
                $this->values [] = $item;
            }
        }
        return $this;
    }

    protected function buildValues(Quoter $quoter)
    {
        $result = '';
        if ($this->values) {
            $fields = array();
            foreach ($this->values as $row) {
                foreach ($row as $field => $value) {
                    $fields[$field] = $field;
                }
            }
            $result .= ' (';
            if ($fields) {
                foreach ($fields as $field) {
                    $result .= $quoter->quote(new Symbol($field)) . ', ';
                }
                $result = substr($result, 0, -2);
            }
            $result .= ') VALUES ';

            foreach ($this->values as $row) {
                $rowString = '';
                foreach ($fields as $field) {
                    if (array_key_exists($field, $row)) {
                        $value = $row[$field];
                    } else {
                        $value = new DefaultValue();
                    }

                    $rowString .= $quoter->quote($value) . ', ';
                }
                $result .= '(' . substr($rowString, 0, -2) . '), ';
            }
            $result = substr($result, 0, -2);
            return $result;
        } else {
            return $result;
        }
    }

}
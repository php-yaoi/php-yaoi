<?php

class Sql_SelectStatement extends Sql_Statement {
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
    protected $select = array();

    protected $from = array();

    protected $where = array(); // array of ored expressions


    public function __construct($columns = null) {
        if (null !== $columns) {
            $this->select($columns);
        }
    }

    private function setExpression($expression, $as, &$store) {
        if ($as) {
            $store [$as]= $expression;
        }
        else {
            $store []= $expression;
        }
    }

    public function select($expression, $as = null) {
        $this->setExpression($expression, $as, $this->select);
        return $this;
    }


    public function from($expression, $as = null) {
        $this->setExpression($expression, $as, $this->from);
        return $this;
    }

    public function where($expression, $as = null) {
        $this->setExpression($expression, $as, $this->where);
        return $this;
    }

    protected $union = array();
    const UNION_TYPE_ALL = 'a';
    public function unionAll(Sql_SelectStatement $select, $as = null) {
        $this->setExpression(array(self::UNION_TYPE_ALL, $select), $as, $this->union);
    }

    const UNION_TYPE_UNIQUE = 'u';
    public function union(Sql_SelectStatement $select, $as = null) {
        $this->setExpression(array(self::UNION_TYPE_UNIQUE, $select), $as, $this->union);
    }


    // TODO continue later
    public function build() {
        $q = "SELECT ";
        if ($this->from) {
            foreach ($this->from as $as => $expression) {
                if (is_string($as)) {
                    //$q .= (string)$table . ' AS ' .
                }
            }
        }
    }
}
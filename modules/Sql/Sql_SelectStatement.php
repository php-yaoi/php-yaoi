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
    protected $select = null;

    protected $from = array();

    protected $where = array(); // array of ored expressions


    public function __construct($from = null) {
        if (null !== $from) {
            $this->from($from);
        }
    }

    private function setExpression($expression, &$store) {
        $store []= $expression;
    }

    public function columns($expression) {
        $this->setExpression($expression, $this->select);
        return $this;
    }


    public function from($expression) {
        $this->setExpression($expression, $this->from);
        return $this;
    }

    public function where($expression) {
        $this->setExpression($expression, $this->where);
        return $this;
    }

    protected $union = array();
    const UNION_TYPE_ALL = 'a';
    public function unionAll(Sql_SelectStatement $select, $as = null) {
        $this->setExpression(array(self::UNION_TYPE_ALL, $select), $this->union);
    }

    const UNION_TYPE_UNIQUE = 'u';
    public function union(Sql_SelectStatement $select, $as = null) {
        $this->setExpression(array(self::UNION_TYPE_UNIQUE, $select), $this->union);
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
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


    public function from($expression, $as = null, &$reference = null) {
        if (is_null($as)) {
            $this->from []= $expression;
        }
        else {
            $this->from[$as] = $expression;
        }
        return $this;
    }




}
<?php

class Sql_Statement {
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
    /**
     * @var string
     */
    protected $literalStatement;

    public function __construct($literalStatement = null) {
        $this->literalStatement = $literalStatement;
    }


    protected function render() {
        return $this;
    }

    public function __toString() {
        return $this->literalStatement;
    }
}
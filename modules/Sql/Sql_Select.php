<?php
/**
 * Class Sql_Select
 * @deprecated
 */
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
     * @deprecated
     * @param $expression
     * @param null $binds
     * @return $this
     */
    public function columns($expression, $binds = null) {
        $this->select []= Sql_Expression::createFromFuncArguments(func_get_args());
        return $this;
    }



    public function build(Database $client = null) {
        if (null === $client) {
            $client = $this->database;
        }

        $q = "SELECT";

        $q .= $this->buildSelect($client);
        $q .= $this->buildFrom($client);
        $q .= $this->buildJoin($client);
        $q .= $this->buildWhere($client);
        $q .= $this->buildOrder($client);
        $q .= $this->buildLimit();

        return $q;
    }
}


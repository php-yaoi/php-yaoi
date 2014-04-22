<?php

class Sql_Statement extends Base_Class {
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

    /**
     * @var Database
     */
    protected $client;
    public function setClient(Database $client = null) {
        $this->client = $client;
        return $this;
    }
}
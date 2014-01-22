<?php

class Sql_Expression {
    public function __construct($literal, $binds = null) {
        $this->literal = $literal;
        $this->binds = $binds;
    }

    private $literal;
    private $binds;
    private $client;

    public function setDbClient(Database_Client $client) {
        $this->client = $client;
    }


    public function __toString() {
        return $this->literal;
    }
}
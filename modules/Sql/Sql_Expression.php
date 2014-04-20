<?php

class Sql_Expression extends Base_Class {
    public function __construct($literal, $binds = null) {
        if (func_num_args() > 2) {
            $arguments = func_get_args();
            array_shift($arguments);
            $binds = $arguments;
        }
        if (null !== $binds && !is_array($binds)) {
            $binds = array($binds);
        }

        $this->literal = $literal;
        $this->binds = $binds;
    }

    private $literal;
    private $binds;
    private $client;

    public function setDbClient(Database $client) {
        $this->client = $client;
    }


    public function __toString() {
        return (string)$this->literal;
    }
}
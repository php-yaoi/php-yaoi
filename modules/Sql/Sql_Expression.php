<?php

class Sql_Expression extends Base_Class {
    public function __construct($statement, $binds = null) {
        if (func_num_args() > 2) {
            $arguments = func_get_args();
            array_shift($arguments);
            $binds = $arguments;
        }
        if (null !== $binds && !is_array($binds)) {
            $binds = array($binds);
        }

        $this->statement = $statement;
        $this->binds = $binds;
    }

    private $statement;
    private $binds;


    /**
     * @var Database
     */
    private $client;
    public function setDbClient(Database $client) {
        $this->client = $client;
        return $this;
    }

    public function opAnd($expression) {
        return $this;
    }


    public function opOr($expression) {
        return $this;
    }


    public function opXor($expression) {
        return $this;
    }


    public function __toString() {
        if ($this->binds) {
            return $this->client->buildString($this->statement, $this->binds);
        }
        else {
            return $this->statement;
        }
    }
}
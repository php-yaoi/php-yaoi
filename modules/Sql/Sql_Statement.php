<?php

class Sql_Statement extends Sql_Expression {

    /**
     * @var Database
     */
    protected $database;
    public function bindDatabase(Database $client = null) {
        $this->database = $client;
        return $this;
    }

    public function query() {
        return $this->database->query($this);
    }

    public function __toString() {
        return $this->build($this->database);
    }
}
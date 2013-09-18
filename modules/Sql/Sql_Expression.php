<?php

class Sql_Expression {
    private $literal;

    public function __toString() {
        return $this->literal;
    }

}
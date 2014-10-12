<?php


class Sql_Symbol {
    // TODO support complex 'table.field' symbols
    public $name;
    public function __construct($name) {
        $this->name = $name;
    }
} 
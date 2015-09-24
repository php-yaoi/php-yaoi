<?php

namespace Yaoi\String\Lexer;


class Delimiter
{
    public $start;
    public $startLen;

    public function __construct($start) {
        $this->start = $start;
        $this->startLen = strlen($start);
    }
}
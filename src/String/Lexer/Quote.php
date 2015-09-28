<?php

namespace Yaoi\String\Lexer;


class Quote
{
    public $start;
    public $startLen;
    public $end;
    public $endLen;
    public $escape;

    public function __construct($start, $end = null, $escape = array()) {
        $this->start = $start;
        $this->startLen = strlen($start);

        $this->end = $end;
        $this->endLen = strlen($end);

        $this->escape = $escape;
    }

}
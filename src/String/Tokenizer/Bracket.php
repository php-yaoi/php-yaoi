<?php

namespace Yaoi\String\Tokenizer;

class Bracket
{
    public $start;
    public $startLen;
    public $end;
    public $endLen;

    public function __construct($start, $end) {
        $this->start = $start;
        $this->startLen = strlen($start);
        $this->end = $end;
        $this->endLen = strlen($end);
    }

}
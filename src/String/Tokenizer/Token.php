<?php

namespace Yaoi\String\Tokenizer;


class Token
{
    public $start;
    public $deQuoted;
    public $original;
    public $end;

    public function __construct($deQuoted, $start, $end = null, $original = null) {
        $this->deQuoted = $deQuoted;
        $this->start = $start;
        $this->end = $end;
        $this->original = null === $original ? $deQuoted : $original;
    }
}
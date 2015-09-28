<?php

namespace Yaoi\String\Lexer;


class Token
{
    public $start;
    public $unEscapedContent;
    public $escapedContent;
    public $end;

    public function __construct($unEscapedContent, $start, $end = null, $escapedContent = null) {
        $this->unEscapedContent = $unEscapedContent;
        $this->start = $start;
        $this->end = $end;
        $this->escapedContent = null === $escapedContent ? $unEscapedContent : $escapedContent;
    }
}
<?php

namespace Yaoi\String\Lexer;

use Yaoi\String\Exception;

class Quoter implements \Yaoi\String\Quoter
{

    public function quote($value)
    {
        if ($value instanceof Token) {
            return $value->start . $value->escapedContent . $value->end;
        }
        else {
            throw new Exception('Bad argument', Exception::BAD_ARGUMENT);
        }
    }
}
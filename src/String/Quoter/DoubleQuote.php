<?php

namespace Yaoi\String\Quoter;

use Yaoi\String\Quoter;

class DoubleQuote implements Quoter
{
    public function quote($value)
    {
        return '"' . str_replace('"', '\\"', $value) . '"';
    }

}
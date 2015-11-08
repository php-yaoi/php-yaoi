<?php

namespace Yaoi\Cli;



class UnnamedArgument extends Option
{


    public function getUsage() {
        $usage = ($this->required ? '<' : '[')
            . $this->name . ($this->isVariadic ? '...' : '')
            . ($this->required ? '>' : ']');
        return $usage;
    }
}
<?php

namespace Yaoi\Cli;


use Yaoi\Command\Option;

class UnnamedArgument extends Option
{
    public $isVariadic = false;

    public function setIsVariadic($yes = true) {
        $this->isVariadic = $yes;
        return $this;
    }

}
<?php

namespace Yaoi\Cli;

use Yaoi\BaseClass;
use Yaoi\Command;

class Application extends BaseClass
{
    protected $definitions = array();
    public function addCommandDefinition(Command\Definition $commandDefinition) {
        $this->definitions []= $commandDefinition;
    }





}
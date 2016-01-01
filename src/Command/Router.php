<?php

namespace Yaoi\Command;


use Yaoi\Command;
use Yaoi\Io\Request;

abstract class Router extends \Yaoi\Router
{
    /**
     * @var Command[]
     */
    public $commands = array();

    public function addCommand(Command $command) {
        $name = $command->getName();
        if (isset($this->commands[$name])) {
            $definedClass = get_class($this->commands[$name]);
            $newClass = get_class($command);
            throw new Exception("Command $definedClass with name '$name' is already defined, can not add $newClass", Exception::ALREADY_DEFINED);
        }
        $this->commands [$command->getName()]= $command;
        return $this;
    }

}
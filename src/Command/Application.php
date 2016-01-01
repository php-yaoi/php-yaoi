<?php

namespace Yaoi\Command;

use Yaoi\Cli\Option;
use Yaoi\Command;

abstract class Application extends Command implements Command\Application\Contract
{
    public $action;

    static function setUpDefinition(Definition $definition, $options)
    {
        $commandDefinitions = new \stdClass();
        static::setUpCommands($definition, $commandDefinitions);
        $actions = array();



        foreach ((array)$commandDefinitions as $name => $commandDefinition) {
            $actions []= $name;
            $definition->actions [$name]= $commandDefinition;
        }

        $options->action = Option::create()
            ->setEnum($actions)
            ->setDescription('Action name')
            ->setIsRequired()
            ->setIsUnnamed();
    }

    public function performAction()
    {
        var_dump($this);
        return;

        $commandDefinition = static::definition()->actions[$this->action];
        /** @var Command $command */
        $command = new $commandDefinition->commandClass();



        var_dump($command);
        /** @var Runner $runner */
        //$runner = new $runnerClass($command);
        //$runner->init();

    }


}
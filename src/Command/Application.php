<?php

namespace Yaoi\Command;

use Yaoi\Cli\Command\RequestMapper;
use Yaoi\Command;

/**
 * This looks like an ActionSet, not Application
 *
 * Class Application
 * @package Yaoi\Command
 */
abstract class Application extends Command implements Command\Application\Contract
{
    public $action;

    static function setUpDefinition(Definition $definition, $options)
    {
        $commandDefinitions = new \stdClass();
        static::setUpCommands($definition, $commandDefinitions);
        $actions = array();



        foreach ((array)$commandDefinitions as $name => $commandDefinition) {
            $name = RequestMapper::getPublicName($name);
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
        // no op
        // TODO refactor interfaces to remove this method for Application
    }
}
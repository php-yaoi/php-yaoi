<?php

namespace Yaoi\Command\Application;


use Yaoi\Command\Definition;

interface Contract extends \Yaoi\Command\Contract
{
    /**
     * Required setup option types in provided options object
     * @param $definition Definition
     * @param $commandDefinitions static|\stdClass
     */
    static function setUpCommands(Definition $definition, $commandDefinitions);
}
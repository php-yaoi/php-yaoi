<?php

namespace YaoiTests\Helper\Command;


use Yaoi\Command;
use Yaoi\Command\Definition;

class TestCommandWithVersion extends Command
{
    public function performAction()
    {
    }

    /**
     * Required setup option types in provided options object
     * @param $definition Definition
     * @param $options static|\stdClass
     */
    static function setUpDefinition(Definition $definition, $options)
    {
        $definition->description = 'Test command with version';
        $definition->name = 'cli-cli-cli';
        $definition->version = 'v1.0';
    }

}
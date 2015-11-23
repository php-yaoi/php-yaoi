<?php

namespace YaoiTests\Helper\Command;


use Yaoi\Cli\Command;
use Yaoi\Cli\Option;
use Yaoi\Command\Definition;

class TestCommandWithRequiredArgument extends Command
{
    public $argument;
    public $argumentTwo;
    public $option;

    protected function performAction()
    {
    }

    /**
     * Required setup option types in provided options object
     * @param $definition Definition
     * @param $options static|\stdClass
     */
    static function setUpDefinition(Definition $definition, $options)
    {
        $options->argument = Option::create()->setIsUnnamed()->setIsRequired();
        $options->argumentTwo = Option::create()->setIsUnnamed()->setIsRequired()->setIsVariadic();
        $options->option = Option::create();
    }


}
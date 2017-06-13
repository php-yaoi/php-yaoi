<?php

namespace YaoiTests\Helper\Command;


use Yaoi\Cli\Option;
use Yaoi\Command;
use Yaoi\Command\Definition;

class TestCommandWithVariadicError extends Command
{
    public $variadicArgument;
    public $argument;

    /**
     * Required setup option types in provided options object
     * @param $definition Definition
     * @param $options static|\stdClass
     */
    static function setUpDefinition(Definition $definition, $options)
    {
        $options->variadicArgument = Option::create()->setIsVariadic()->setIsUnnamed();
        $options->argument = Option::create()->setIsUnnamed();
    }

    public function performAction()
    {
        print_r($this);
    }

}
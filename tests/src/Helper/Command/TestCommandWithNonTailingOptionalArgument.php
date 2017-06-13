<?php

namespace YaoiTests\Helper\Command;


use Yaoi\Cli\Option;
use Yaoi\Command;
use Yaoi\Command\Definition;

class TestCommandWithNonTailingOptionalArgument extends Command
{
    public $requiredArgument;
    public $optionalArgument;

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
        $options->optionalArgument = Option::create()->setIsUnnamed();
        $options->requiredArgument = Option::create()->setIsUnnamed()->setIsRequired();
    }

}
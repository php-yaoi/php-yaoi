<?php

namespace YaoiTests\Helper\Command;


use Yaoi\Cli\Command;
use Yaoi\Command\Definition;
use Yaoi\Cli\Option;

class TestCommandWithOptionValue extends Command
{
    public $boolOption;
    public $valueOption;
    public $parentOption;

    protected function performAction()
    {
        print_r($this);
    }

    /**
     * Required setup option types in provided options object
     * @param $definition Definition
     * @param $options static|\stdClass
     */
    static function setUpDefinition(Definition $definition, $options)
    {
        $options->valueOption = Option::create()->setType();
        $options->boolOption = Option::create();
        $options->parentOption = \Yaoi\Command\Option::create();
    }


}
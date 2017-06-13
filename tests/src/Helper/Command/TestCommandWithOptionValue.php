<?php

namespace YaoiTests\Helper\Command;


use Yaoi\Cli\Option;
use Yaoi\Command;
use Yaoi\Command\Definition;

class TestCommandWithOptionValue extends Command
{
    public $boolOption;
    public $valueOption;
    public $unifiedOption;

    public function performAction()
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
        $options->unifiedOption = \Yaoi\Command\Option::create();
    }


}
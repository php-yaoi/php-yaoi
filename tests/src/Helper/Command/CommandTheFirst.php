<?php

namespace YaoiTests\Helper\Command;


use Yaoi\Cli\Command;
use Yaoi\Cli\UnnamedArgument;
use Yaoi\Cli\Option;
use Yaoi\Command\Definition;

class CommandTheFirst extends Command
{
    public $action;
    public $argumentA;
    public $argumentB;
    public $optionC;
    public $optionD;
    public $someEnum;

    /**
     * Required setup option types in provided options object
     * @param $definition Definition
     * @param $options static|\stdClass
     */
    static function setUpDefinition(Definition $definition, $options)
    {
        $definition->description = 'This is a command one for doing nothing';
        $definition->name = 'the-first';

        $options->action = UnnamedArgument::create()->setRequired()
            ->setEnum('get', 'delete', 'create')
            ->setDescription('Main action');

        $options->argumentA = UnnamedArgument::create()->setDescription('Bee description follows');

        $options->argumentB = UnnamedArgument::create()->setIsVariadic()
            ->setDescription('This is a variadic argument');

        $options->optionC = Option::create()
            ->setDescription('Some option for the C');

        $options->optionD = Option::create()->setRequired()
            ->setShortName('d')->setType()
            ->setDescription('Short name option with required value');

        $options->someEnum = Option::create()->setRequired()
            ->setEnum('one', 'two', 'three')
            ->setDescription('Enumerated option to set up something');
    }

    public function execute()
    {
        print_r($this);
    }

}
<?php

namespace YaoiTests\Helper\Command;


use Yaoi\Cli\Command;
use Yaoi\Cli\UnnamedArgument;
use Yaoi\Cli\Option;

class CommandTheFirst extends Command
{
    public $argumentA;
    public $argumentB;
    public $optionC;
    public $optionD;

    /**
     * @param static|\stdClass $options
     * @return void
     */
    public function setUpOptions($options)
    {
        $options->argumentA = UnnamedArgument::create()->setRequired();
        $options->argumentB = UnnamedArgument::create()->setIsVariadic();
        $options->optionC = Option::create();
        $options->optionD = Option::create()->setRequired()->setShortName('d')->setType();
    }

    public function getDescription()
    {
        return 'This is a command one for doing nothing';
    }

    public function getName()
    {
        return 'the-first';
    }

    public function execute()
    {
        print_r($this);
    }

}
<?php

namespace YaoiTests\Helper\Command;


use Yaoi\Command;

class CommandTwo extends Command
{
    public $optionD;

    /**
     * @param static|\stdClass $options
     * @return void
     */
    public function setUpDefinition($options)
    {
        $options->optionD = Command\Option::create();
    }

    public function getDescription()
    {
        return 'The Two';
    }

    public function getName()
    {
        return 'two';
    }

    public function execute()
    {
        echo "Executing two", PHP_EOL;
    }


}
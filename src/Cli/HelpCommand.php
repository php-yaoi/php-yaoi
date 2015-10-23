<?php

namespace Yaoi\Cli;

class HelpCommand extends Command
{
    public $command;

    /**
     * @param static|\stdClass $options
     * @return void
     */
    public function setUpOptions($options)
    {
        $options->command = UnnamedArgument::create()->setEnum();
        // TODO: Implement setUpOptions() method.
    }

    public function getDescription()
    {
        // TODO: Implement getDescription() method.
    }

    public function getName()
    {
        // TODO: Implement getName() method.
    }

    public function execute()
    {
        // TODO: Implement execute() method.
    }
}
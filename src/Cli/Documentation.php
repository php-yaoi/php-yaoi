<?php

namespace Yaoi\Cli;

use Yaoi\BaseClass;
use Yaoi\Cli\View\Table;
use Yaoi\Log;

class Documentation extends BaseClass
{

    protected $arguments;
    /** @var  Command[] */
    protected $commands;
    protected $output;

    public function __construct($arguments, $commands) {
        $this->arguments = $arguments;
        $this->commands = $commands;
        $this->output = new Console();
    }

    public function showUsage() {
        $this->output->set(Console::BOLD)->printLine("Usage: ")->set();
        $this->output->addPadding();
        $commandsTable = new Table();
        foreach ($this->commands as $name => $command) {
            $commandsTable->addRow(array($name, $command->getDescription()));
        }
        foreach ($commandsTable->getLines() as $line) {
            $this->output->printLine($line);
        }

        $this->output->addPadding('');
        $this->output->eol()->eol();
        return;

    }
}
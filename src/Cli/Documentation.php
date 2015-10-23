<?php

namespace Yaoi\Cli;

use Yaoi\BaseClass;
use Yaoi\Console\Colored;
use Yaoi\Log;

class Documentation extends BaseClass
{
    protected $arguments;
    protected $commands;
    protected $output;

    public function __construct($arguments, $commands) {
        $this->arguments = $arguments;
        $this->commands = $commands;
        $this->output = new Log('colored-stdout');
        echo Colored::get('test', Colored::BG_GREEN, Colored::BG_CYAN);
    }

    public function showUsage() {
        $this->output->push('Ololo!', Log::TYPE_ERROR);
        $this->output->push('Ololo2!', Log::TYPE_SUCCESS);
        $this->output->push('Ololo2!', Log::TYPE_MESSAGE);
        //$helpCommand = isset($argv[2]) ? $argv[2] : null;
        //$this->help($helpCommand);
        return;

    }
}
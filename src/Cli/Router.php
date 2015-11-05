<?php

namespace Yaoi\Cli;

use Yaoi\Command;
use Yaoi\Request;

class Router extends Command\Router
{
    const HELP = 'help';

    public function route(Request $request) {
        $argv = $request->server()->argv;
        $commandName = isset($argv[1]) ? $argv[1] : self::HELP;

        if (self::HELP === $commandName) {
            $documentation = new Documentation($argv, $this->commands);
            $documentation->showUsage();
        }
    }



    public function help($helpCommand = null) {
        if ($helpCommand) {
            if (!isset($this->commands[$helpCommand])) {
                $this->error("Command '$helpCommand' not found");
                $this->helpMessage();
            }
            else {
                $command = $this->commands[$helpCommand];
                echo 'Usage: ';
                $this->echoCommandSynopsis($command);
                echo PHP_EOL;
            }
        }
        else {
            $this->helpMessage();
        }
    }

    protected function helpMessage() {
        echo "Usage: ", PHP_EOL;

        foreach ($this->commands as $name => $command) {
            echo "\t", $name, "\t\t", $command->getDescription(), PHP_EOL;
        }
    }


    protected function helpCommand(Command $command) {
        echo $command->getDescription(), PHP_EOL;

    }

    protected function echoCommandSynopsis(Command $command) {
        //print_r($command);
        foreach ($command->options(true) as $option) {
            $this->helpOption($option);
        }
    }

    private function helpOption(Command\Option $option) {
        if ($option->required) {
            if (!$option instanceof UnnamedArgument) {
                echo ' ';
                $this->helpOptionName($option);
                return;
            }
            echo ' <';
            $this->helpOptionName($option);
            echo '>';
        }
        else {
            echo ' [';
            $this->helpOptionName($option);
            echo ']';
        }
    }

    private function helpOptionName(Command\Option $option) {
        if ($option instanceof UnnamedArgument) {
            echo $option->name, ($option->isVariadic ? '...' : '');
            return;
        }

        if ($option instanceof Option && $option->shortName) {
            echo \Yaoi\Cli\Command::OPTION_SHORT, $option->shortName;
        }
        else {
            echo \Yaoi\Cli\Command::OPTION_NAME, $option->name;
        }

        if ($option->type === Option::TYPE_VALUE) {
            echo ' <' . $option->name . '>';
        }

    }

    protected function error($message) {
        echo 'ERROR: ', $message, PHP_EOL;
    }
}
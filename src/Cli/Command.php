<?php

namespace Yaoi\Cli;

use Yaoi\Cli\View\Table;
use Yaoi\Command\Exception;
use Yaoi\Command\Option;
use Yaoi\Request;

abstract class Command extends \Yaoi\Command
{
    const OPTION_NAME = '--';
    const OPTION_SHORT = '-';
    public function init(Request $request) {
        $tokens = $request->server()->argv;
        $argc = count($tokens);
        for ($index = 0; $index < $argc; ++$index) {
            $token = new StringVar($tokens[$index]);
            $option = null;


            if ($optionName = $token->afterStarts(self::OPTION_NAME)) {
                if (!isset($this->optionsByName[$optionName])) {
                    throw new Exception('Unknown option "' . self::OPTION_NAME . $optionName . '"', Exception::UNKNOWN_OPTION);
                }
                $option = $this->optionsByName[$optionName];
            }

            elseif ($optionName = $token->afterStarts(self::OPTION_SHORT)) {
                if (!isset($this->optionsByName[$optionName])) {
                    throw new Exception('Unknown option "' . self::OPTION_NAME . $optionName . '"', Exception::UNKNOWN_OPTION);
                }
                $option = $this->optionsByShortName[$optionName];
            }

            if (null === $option) {
                $this->arguments;


            }

        }
    }

    private static $headingColor = Console::FG_BLUE;
    public static function help() {
        $definition = static::definition();
        $console = new Console();
        $console
            ->set(self::$headingColor, Console::BOLD)->printLine($definition->name)
            ->set()->printLine($definition->description);

        $usage = '';
        //print_r($definition->options);
        $optionsDescription = array();
        foreach ((array)$definition->options as $name => $option) {
            if (!$option instanceof Option) {
                $option = Option::cast($option);
            }

            if ($option instanceof UnnamedArgument) {
                $usage .= ' ' . $option->getUsage();
            }
            elseif ($option instanceof Option && $option->required) {
                $usage .= ' ' . $option->getUsage();
            }
            //else {
                $optionsDescription []= array($option->getUsage(), $option->description);
            //}

        }

        $console->eol()->set(self::$headingColor)->printLine("Usage: ")
            ->set()->setPadding('   ')->printLine($usage)->setPadding('');
        $console->eol()->set(self::$headingColor)->printLine('Options: ')
            ->set()->setPadding('   ')
            ->printLines(Table::create(new \ArrayIterator($optionsDescription)));
    }

}
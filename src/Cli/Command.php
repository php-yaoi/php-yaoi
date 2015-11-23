<?php

namespace Yaoi\Cli;

use Yaoi\Cli\Command\PrepareDefinition;
use Yaoi\Cli\View\Table;
use Yaoi\Request;
use Yaoi\String\Expression;
use Yaoi\String\StringValue;
use Yaoi\View\Semantic\Error;
use Yaoi\View\Semantic\Heading;
use Yaoi\View\Semantic\Info;
use Yaoi\View\Semantic\Success;
use \Yaoi\Cli\View\Text as ViewText;

abstract class Command extends \Yaoi\Command
{
    public $help;
    public $version;

    const OPTION_NAME = '--';
    const OPTION_SHORT = '-';
    public function init(Request $request) {
        $options = static::optionsArray();
        $def = new PrepareDefinition($options);

        $tokens = $request->server()->argv;
        $scriptName = array_shift($tokens);
        $tokens = array_values($tokens);

        $argc = count($tokens);

        if ($argc === 1) {
            if ($tokens[0] === self::OPTION_NAME . Option::cast(static::options()->help)->getName()) {
                $this->help = true;
                return $this;
            }

            if ($tokens[0] === self::OPTION_NAME . Option::cast(static::options()->version)->getName()) {
                $this->version = true;
                return $this;
            }
        }

        $variadicStarted = false;
        $variadicValues = array();
        $valueRequired = false;

        /** @var Option $option */
        $option = null;

        for ($index = 0; $index < $argc; ++$index) {
            $token = new StringValue($tokens[$index]);

            $optionFound = null;
            if (($optionName = $token->afterStarts(static::OPTION_NAME)) && isset($def->byName[$optionName])) {
                $optionFound = $def->byName[$optionName];
            }
            elseif (($optionName = $token->afterStarts(static::OPTION_SHORT)) && isset($def->byShortName[$optionName])) {
                $optionFound = $def->byShortName[$optionName];
            }

            if ($variadicStarted && $optionFound) {
                if (!$variadicValues) {
                    throw new Exception('Unexpected option, value required', Exception::VALUE_REQUIRED);
                }
                $this->{$option->name} = $variadicValues;
                $variadicValues = array();
                $variadicStarted = false;
            }

            if ($def->requiredArguments && $optionFound) {
                throw new Exception('Unexpected option, argument required', Exception::ARGUMENT_REQUIRED);
            }

            if ($variadicStarted) {
                $variadicValues []= $option->validateFilterValue((string)$token);
                continue;
            }

            if ($valueRequired) {
                if ($optionFound) {
                    throw new Exception('Unexpected option, value required', Exception::VALUE_REQUIRED);
                }
                $this->{$option->name} = $option->validateFilterValue((string)$token);
                $valueRequired = false;
                continue;
            }

            if ($def->requiredArguments) {
                /** @var Option $option */
                $option = array_shift($def->requiredArguments);
                $value = $option->validateFilterValue((string)$token);
                if ($option->isVariadic) {
                    $variadicStarted = true;
                    $variadicValues []= $value;
                    continue;
                }
                else {
                    $this->{$option->name} = $value;
                    continue;
                }
            }

            if ($optionFound) {
                $option = $optionFound;

                if ($option->isRequired) {
                    unset($def->requiredOptions[$option->name]);
                }

                $def->optionalArguments = false;
                if ($option->type === Option::TYPE_BOOL) {
                    $this->{$option->name} = true;
                    continue;
                }
                if ($option->isVariadic) {
                    $variadicStarted = true;
                    continue;
                }
                else {
                    $valueRequired = true;
                    continue;
                }
            }

            if ($def->optionalArguments) {
                /** @var Option $option */
                $option = array_shift($def->optionalArguments);
                if ($option->isVariadic) {
                    $variadicStarted = true;
                    $variadicValues []= $option->validateFilterValue((string)$token);
                    continue;
                }
                else {
                    $this->{$option->name} = $option->validateFilterValue((string)$token);
                    continue;
                }
            }

            throw new Exception('Unexpected token: ' . $token, Exception::UNKNOWN_OPTION);

        }

        if ($variadicStarted) {
            $this->{$option->name} = $variadicValues;
        }

        if ($def->requiredArguments) {
            foreach ($def->requiredArguments as $option) {
                throw new Exception('Missing required argument: ' . $option->getUsage(), Exception::ARGUMENT_REQUIRED);
            }
        }

        if ($def->requiredOptions) {
            foreach ($def->requiredOptions as $option) {
                throw new Exception('Option required: ' . $option->getUsage(), Exception::OPTION_REQUIRED);
            }
        }


        return $this;
    }

    public static function showVersion() {
        $definition = static::definition();
        $console = new Console();
        $console->eol();
        if ($definition->name) {
            if ($definition->version) {
                ViewText::create(new Heading($definition->version . ' '))->render();
            }

            ViewText::create(new Heading($definition->name))->render();
            $console->eol();
        }
        if ($definition->description) {
            $console->printLine($definition->description)->eol();
        }
    }

    public static function showHelp() {
        $definition = static::definition();
        $console = new Console();
        self::showVersion();

        try {
            new PrepareDefinition(static::optionsArray());
        }
        catch (Exception $exception) {
            self::error('Command definition error: ' . $exception->getMessage());
            return;
        }

        $usage = '';
        $optionsDescription = array();
        $argumentsDescription = array();
        foreach ((array)$definition->options as $name => $option) {
            if (!$option instanceof Option) {
                $option = Option::cast($option);
            }

            if ($option instanceof Option) {
                if ($option->isUnnamed || $option->isRequired) {
                    $usage .= ' ' . $option->getUsage();
                }

                $description = $option->description;
                if ($option->type === Option::TYPE_ENUM) {
                    $description .= ($description ? PHP_EOL : '') . 'Allowed values: ' . implode(', ', $option->values);
                }

                if ($option->isUnnamed) {
                    $argumentsDescription []= array(new Info($option->name), $description);

                }
                else {
                    $optionsDescription []= array(new Info($option->getUsage()), $description);
                }
            }

        }

        ViewText::create(new Heading("Usage: "))->render();
        $console->eol()->setPadding('   ')->printLine($usage)->setPadding('');

        if ($argumentsDescription) {
            $console->eol()->setPadding('   ')
                ->printLines(Table::create(new \ArrayIterator($argumentsDescription)));

        }

        $console->setPadding('');
        if ($optionsDescription) {
            ViewText::create(new Heading("Options: "))->render();
            $console->eol()->setPadding('   ')
                ->printLines(Table::create(new \ArrayIterator($optionsDescription)));
        }
    }

    public static function error($message, $binds = null) {
        if ($binds !== null) {
            $message = (string)Expression::create(func_get_args());
        }
        ViewText::create(new Error($message))->render();
        Console::getInstance()->eol();
    }

    public static function success($message, $binds = null) {
        if ($binds !== null) {
            $message = (string)Expression::create(func_get_args());
        }
        ViewText::create(new Success($message))->render();
        Console::getInstance()->eol();
    }


    protected static function createDefinition() {
        $definition = parent::createDefinition();
        /** @var static $options */
        $options = $definition->options;
        $options->help = Option::create()->setDescription('Show usage information');
        $options->version = Option::create()->setDescription('Show version');
        return $definition;
    }

    public function run()
    {
        if ($this->help) {
            $this->showHelp();
            return;
        }
        elseif ($this->version) {
            $this->showVersion();
            return;
        }
        else {
            $this->performAction();
        }
    }
}
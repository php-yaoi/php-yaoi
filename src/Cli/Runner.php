<?php

namespace Yaoi\Cli;

use Yaoi\BaseClass;
use Yaoi\Cli\Command\PrepareDefinition;
use Yaoi\Command;
use Yaoi\Request;
use Yaoi\String\Expression;
use Yaoi\String\StringValue;
use Yaoi\View\Semantic\Error;
use Yaoi\View\Semantic\Success;
use Yaoi\Cli\View\Table;
use Yaoi\View\Semantic\Heading;
use Yaoi\View\Semantic\Info;
use \Yaoi\Cli\View\Text as ViewText;



class Runner extends BaseClass implements \Yaoi\Command\Runner
{
    const OPTION_NAME = '--';
    const OPTION_SHORT = '-';

    const HELP = 'help';
    const VERSION = 'version';

    /** @var Command */
    protected $command;

    /** @var \Yaoi\Command\Definition  */
    protected $definition;

    /** @var \Yaoi\Command\Option[]  */
    protected $optionsArray;

    protected $console;

    public function __construct(Command $command) {
        $this->command = $command;
        $this->definition = $command->definition();
        $this->optionsArray = $this->command->optionsArray();
        $this->console = new Console();
        $command->setRunner($this);
    }

    protected $showHelp;
    protected $showVersion;

    public function init(Request $request = null, $throw = false)
    {
        if (null === $request) {
            $request = Request::createAuto();
        }

        try {
            $def = new PrepareDefinition($this->optionsArray);

            $tokens = $request->server()->argv;
            $scriptName = array_shift($tokens);
            $tokens = array_values($tokens);

            $argc = count($tokens);

            if ($argc === 1) {
                if ($tokens[0] === self::OPTION_NAME . self::HELP) {
                    $this->showHelp = true;
                    return $this;
                }

                if ($tokens[0] === self::OPTION_NAME . self::VERSION) {
                    $this->showVersion = true;
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
                } elseif (($optionName = $token->afterStarts(static::OPTION_SHORT)) && isset($def->byShortName[$optionName])) {
                    $optionFound = $def->byShortName[$optionName];
                }

                if ($variadicStarted && $optionFound) {
                    if (!$variadicValues) {
                        throw new Exception('Unexpected option, value required', Exception::VALUE_REQUIRED);
                    }
                    $this->command->{$option->name} = $variadicValues;
                    $variadicValues = array();
                    $variadicStarted = false;
                }

                if ($def->requiredArguments && $optionFound) {
                    throw new Exception('Unexpected option, argument required', Exception::ARGUMENT_REQUIRED);
                }

                if ($variadicStarted) {
                    $variadicValues [] = $option->validateFilterValue((string)$token);
                    continue;
                }

                if ($valueRequired) {
                    if ($optionFound) {
                        throw new Exception('Unexpected option, value required', Exception::VALUE_REQUIRED);
                    }
                    $this->command->{$option->name} = $option->validateFilterValue((string)$token);
                    $valueRequired = false;
                    continue;
                }

                if ($def->requiredArguments) {
                    /** @var Option $option */
                    $option = array_shift($def->requiredArguments);
                    $value = $option->validateFilterValue((string)$token);
                    if ($option->isVariadic) {
                        $variadicStarted = true;
                        $variadicValues [] = $value;
                        continue;
                    } else {
                        $this->command->{$option->name} = $value;
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
                        $this->command->{$option->name} = true;
                        continue;
                    }
                    if ($option->isVariadic) {
                        $variadicStarted = true;
                        continue;
                    } else {
                        $valueRequired = true;
                        continue;
                    }
                }

                if ($def->optionalArguments) {
                    /** @var Option $option */
                    $option = array_shift($def->optionalArguments);
                    if ($option->isVariadic) {
                        $variadicStarted = true;
                        $variadicValues [] = $option->validateFilterValue((string)$token);
                        continue;
                    } else {
                        $this->command->{$option->name} = $option->validateFilterValue((string)$token);
                        continue;
                    }
                }

                throw new Exception('Unexpected token: ' . $token, Exception::UNKNOWN_OPTION);

            }

            if ($variadicStarted) {
                $this->command->{$option->name} = $variadicValues;
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

        } catch (Exception $exception) {
            if ($throw) {
                throw $exception;
            }
            else {
                static::error($exception->getMessage());
                $this->showHelp = true;
            }
        }

        return $this;
    }


    public function error($message)
    {
        $this->console->printLines(
            new ViewText(
                new Error(
                    (string)$message
                )
            )
        );
        return $this;
    }

    public function success($message)
    {
        $this->console->printLines(
            new ViewText(
                new Success(
                    (string)$message
                )
            )
        );
        return $this;
    }

    /**
     * @return $this
     */
    public function respond($message)
    {
        $this->console->printLines($message);
        return $this;
    }


    public function run()
    {
        if ($this->showHelp) {
            $this->showHelp();
            return;
        } elseif ($this->showVersion) {
            $this->showVersion();
            return;
        } else {
            $this->command->performAction();
        }
    }


    public function showVersion()
    {
        $this->console->eol();
        if ($this->definition->name) {
            if ($this->definition->version) {
                $this->console->printF(
                    new ViewText(
                        new Heading($this->definition->version . ' ')
                    )
                );
            }

            $this->console->printF(
                new ViewText(new Heading($this->definition->name))
            );
            $this->console->eol();
        }
        if ($this->definition->description) {
            $this->console->printLine($this->definition->description)->eol();
        }
    }

    public function showHelp()
    {
        $this->showVersion();

        try {
            new PrepareDefinition($this->optionsArray);
        } catch (Exception $exception) {
            self::error('Command definition error: ' . $exception->getMessage());
            return;
        }

        $usage = '';
        $optionsDescription = array();
        $argumentsDescription = array();
        foreach ($this->optionsArray as $name => $option) {
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
                    $argumentsDescription [] = array(new Info($option->name), $description);

                } else {
                    $optionsDescription [] = array(new Info($option->getUsage()), $description);
                }
            }
        }
        $help = Option::create()->setDescription('Show usage information')->setName(self::HELP);
        $version = Option::create()->setDescription('Show version')->setName(self::VERSION);

        $optionsDescription []= array(new Info($help->getUsage()), $help->description);
        $optionsDescription []= array(new Info($version->getUsage()), $version->description);

        ViewText::create(new Heading("Usage: "))->render();
        $this->console->eol()->setPadding('   ')->printLine($usage)->setPadding('');

        if ($argumentsDescription) {
            $this->console->eol()->setPadding('   ')
                ->printLines(Table::create(new \ArrayIterator($argumentsDescription)));

        }

        $this->console->setPadding('');
        if ($optionsDescription) {
            ViewText::create(new Heading("Options: "))->render();
            $this->console->eol()->setPadding('   ')
                ->printLines(Table::create(new \ArrayIterator($optionsDescription)));
        }
    }




}
<?php

namespace Yaoi\Cli\Command;

use Yaoi\BaseClass;
use Yaoi\Cli\Exception;
use Yaoi\Command;
use Yaoi\Command\Option;
use Yaoi\Io\Request;
use Yaoi\String\StringValue;

class RequestReader extends BaseClass
{
    public $scriptName;
    public $showHelp;
    public $showVersion;
    public $showBashCompletion;

    public $values = array();

    public $skipFirstTokens = 0;


    public function read(Request $request, array $options) {
        $def = new PrepareDefinition($options);

        $tokens = $request->server()->argv;
        for ($i = 0; $i < $this->skipFirstTokens; ++$i) {
            array_shift($tokens);
        }
        $this->scriptName = array_shift($tokens);
        $tokens = array_values($tokens);

        $argc = count($tokens);

        if ($argc === 1) {
            foreach (array(Runner::HELP, Runner::VERSION, Runner::BASH_COMPLETION, Runner::INSTALL) as $builtIn) {
                if ($tokens[0] === Runner::OPTION_NAME . $builtIn) {
                    $this->values[$builtIn] = true;
                    return $this;
                }
            }
        }

        $variadicStarted = false;
        $variadicValues = array();
        $valueRequired = false;

        /** @var \Yaoi\Cli\Option $option */
        $option = null;

        for ($index = 0; $index < $argc; ++$index) {
            $token = new StringValue($tokens[$index]);

            $optionFound = null;
            if (($optionName = $token->afterStarts(Runner::OPTION_NAME)) && isset($def->byName[$optionName])) {
                $optionFound = $def->byName[$optionName];
            } elseif (($optionName = $token->afterStarts(Runner::OPTION_SHORT)) && isset($def->byShortName[$optionName])) {
                $optionFound = $def->byShortName[$optionName];
            }

            if ($variadicStarted && $optionFound) {
                if (!$variadicValues) {
                    throw new Exception('Unexpected option, value required', Exception::VALUE_REQUIRED);
                }
                $this->values[$option->name] = $variadicValues;
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
                $this->values[$option->name] = $option->validateFilterValue((string)$token);
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
                    $this->values[$option->name] = $value;
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
                    $this->values[$option->name] = true;
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
                    $this->values[$option->name] = $option->validateFilterValue((string)$token);
                    continue;
                }
            }

            throw new Exception('Unexpected token: ' . $token, Exception::UNKNOWN_OPTION);
        }

        if ($variadicStarted) {
            $this->values[$option->name] = $variadicValues;
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

    }
}
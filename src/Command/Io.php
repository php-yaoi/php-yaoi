<?php

namespace Yaoi\Command;

use Yaoi\BaseClass;
use Yaoi\Command;
use Yaoi\Command\Option;
use Yaoi\Io\Request;
use Yaoi\Io\Response;
use Yaoi\String\Utils;
use Yaoi\Undefined;

class Io extends BaseClass
{
    public $globalState;

    /** @var Request */
    protected $request;

    /** @var Response  */
    protected $response;

    /** @var  Command\Definition */
    protected $definition;

    /** @var  Option[] */
    protected $globalOptions = array();

    protected $commandStates = array();

    /** @var Command */
    public $command;

    public function __construct(Command\Definition $definition, Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
        $this->definition = $definition;
        $this->globalState = new \stdClass();

        $this->command = $this->prepareCommand($definition);
    }

    protected function prepareCommand(Command\Definition $definition) {
        $commandClass = $definition->commandClass;

        /** @var Command $command */
        $command = new $commandClass;
        $command->setResponse($this->response);
        $command->io = $this;

        $commandOptions = $definition->optionsArray();
        $commandState = $this->readOptions($commandOptions);
        $this->commandStates[$definition->commandClass] = $commandState;

        foreach ($commandOptions as $option) {
            $this->globalOptions [$option->name]= $option; // todo consider managing overlapping options
            if (!isset($commandState->{$option->name})) {
                continue;
            }
            $value = $commandState->{$option->name};
            $this->globalState->{$option->name} = $value;

            if ($option->type === Option::TYPE_ENUM) {
                if ($value instanceof Command\Definition) {
                    $command->{$option->name} = $this->prepareCommand($value);
                }
            }
        }

        return $command;
    }



    public function renderUri(Command $commandState) {
        Command::cast($commandState)->createState();
    }

    /**
     * @param Option[] $commandOptions
     * @return \stdClass
     * @throws Command\Exception
     */
    protected function readOptions(array $commandOptions)
    {
        $commandState = new \stdClass();

        foreach ($commandOptions as $option) {
            $publicName = $this->getPublicName($option->name);
            if (false !== ($value = $this->request->request($publicName, false)
                )
            ) {

                if (Option::TYPE_ENUM === $option->type) {
                    $valueFound = false;
                    foreach ($option->values as $enumName => $enumValue) {
                        $enumPublicName = $this->getPublicName($enumName);
                        if ($enumPublicName === $value) {
                            $valueFound = true;
                            $value = $enumValue;
                            break;
                        }
                    }
                    if (!$valueFound) {
                        throw new Command\Exception('Invalid value for ' . $publicName, Command\Exception::INVALID_VALUE);
                    }
                }

                if (!$value && Option::TYPE_VALUE === $option->type) {
                    throw new Command\Exception('Value required for ' . $publicName, Command\Exception::VALUE_REQUIRED);
                }

                if ($option->isVariadic) {
                    if (!is_array($value)) {
                        $value = array($value);
                    }
                }

                if (Option::TYPE_BOOL === $option->type) {
                    $value = (bool)$value;
                }

                $commandState->{$option->name} = $value;
            }
            else {
                if ($option->isRequired) {
                    throw new Command\Exception('Option '. $publicName .' required', Command\Exception::OPTION_REQUIRED);
                }
            }
        }

        return $commandState;
    }


    public static function getPublicName($name)
    {
        return Utils::fromCamelCase($name, '_');
    }

    public function makeUri(Command $command)
    {
        $url = $this->basePath;

        if (isset($this->parent)) {
        }

        $values = array();
        foreach ($command->optionsArray() as $name => $option) {
            if (!$command->$name instanceof Undefined) {
                $values[$name] = $command->$name;
            }
        }
        $url .= '?' . http_build_query($values);
        return $url;
    }
}
<?php

namespace Yaoi\Command;

use Yaoi\BaseClass;
use Yaoi\Command;
use Yaoi\Io\Response;
use Yaoi\String\Expression;

class Io extends BaseClass
{
    public $globalState;

    /** @var RequestMapperContract */
    protected $requestMapper;

    /** @var Response */
    protected $response;

    /** @var  Command\Definition */
    protected $definition;

    /** @var  Option[] */
    protected $globalOptions = array();

    /** @var State[] */
    protected $commandStates = array();

    /** @var State[] */
    protected $requestStates = array();

    /** @var Command */
    public $command;

    /**
     * @return Command
     */
    public function getCommand()
    {
        return $this->command;
    }

    public function getRequestMapper()
    {
        return $this->requestMapper;
    }

    public function __construct(Definition $definition, RequestMapperContract $requestMapper, Response $response)
    {
        $this->requestMapper = $requestMapper;
        $this->response = $response;
        $this->definition = $definition;
        $this->globalState = new \stdClass();

        $this->makeDefinitionsTree($definition);
        //var_dump($this->definitionTree);

        $this->command = $this->prepareCommand($definition);
    }


    private $definitionTree = array();

    private function makeDefinitionsTree(Definition $definition)
    {
        foreach ($definition->optionsArray() as $option) {
            if ($option->type === Option::TYPE_ENUM) {
                foreach ($option->enumValues as $enumName => $value) {
                    if ($value instanceof Definition) {
                        $this->definitionTree [$value->commandClass] =
                            array($definition->commandClass, $option->name, $enumName);
                        $this->makeDefinitionsTree($value);
                    }
                }
            }
        }
    }

    /**
     * @param State $commandState
     * @return Expression
     * @todo add verbosity on missing command in app tree
     */
    public function makeAnchor(State $commandState)
    {
        $commandClass = $commandState->commandClass;
        $commandClasses = array();
        if ($commandClass !== $this->definition->commandClass) {
            while (isset($this->definitionTree[$commandClass])) {
                $commandClass = $this->definitionTree[$commandClass];
                $commandClasses[] = $commandClass; // TODO rename $commandClass as it is a structure
                $commandClass = $commandClass[0];
            }
        }

        $properties = array();
        for ($i = count($commandClasses) - 1; $i >= 0; --$i) {
            /** @var Command|string $commandClass */
            list($commandClass, $optionName, $enumName) = $commandClasses[$i];
            $optionsArray = $commandClass::definition()->optionsArray();

            if (isset($this->requestStates[$commandClass])) {
                $commandStateArray = $this->requestStates[$commandClass]->export();
            } else {
                $commandStateArray = null;
            }

            if ($commandStateArray) {
                foreach ($commandStateArray as $name => $value) {
                    if (!isset($optionsArray[$name])) {
                        continue;
                    }

                    if ($name === $optionName) {
                        $properties[] = array($optionsArray[$name], $enumName);
                    } else {
                        $properties[] = array($optionsArray[$name], $value);
                    }
                }
            } else {
                if (isset($optionsArray[$optionName])) {
                    $properties[] = array($optionsArray[$optionName], $enumName);
                }
            }
        }

        $commandClass = $commandState->commandClass;
        $optionsArray = $commandClass::definition()->optionsArray();

        foreach ($commandState->export() as $name => $value) {
            if (!isset($optionsArray[$name])) {
                continue;
            }
            $properties[] = array($optionsArray[$name], $value);
        }

        return $this->requestMapper->makeAnchor($properties);
    }


    /**
     * @param string|Command $commandClass
     * @return false|Command
     */
    public function getCommandState($commandClass)
    {
        if (isset($this->commandStates[$commandClass])) {
            return $this->commandStates[$commandClass];
        }
        return false;
    }

    public function getRequestState($commandClass)
    {
        if (isset($this->requestStates[$commandClass])) {
            return $this->requestStates[$commandClass];
        }
        return false;
    }

    protected function prepareCommand(Command\Definition $definition)
    {
        $commandClass = $definition->commandClass;

        /** @var Command $command */
        $command = new $commandClass;
        $command->setResponse($this->response);
        $command->setRequestMapper($this->requestMapper);
        $command->setIo($this);

        $commandOptions = $definition->optionsArray();
        $commandState = new State();
        $commandState->setIo($this);
        $commandState->commandClass = $commandClass;

        $requestState = new State();
        $requestState->setIo($this);
        $requestState->commandClass = $commandClass;


        $this->requestMapper->readOptions($commandOptions, $commandState, $requestState);
        $this->commandStates[$definition->commandClass] = $commandState;
        $this->requestStates[$definition->commandClass] = $requestState;

        foreach ($commandOptions as $option) {
            $this->globalOptions [$option->name] = $option; // todo consider managing overlapping options
            if (!$commandState->hasProperty($option->name)) {
                continue;
            }
            $value = $commandState->{$option->name};
            //var_dump($option->name, $value);
            $this->globalState->{$option->name} = $value;

            $command->{$option->name} = $value;

            if ($option->type === Option::TYPE_ENUM) {
                if ($value instanceof Command\Definition) {
                    $command->{$option->name} = $this->prepareCommand($value);
                }
            }
        }

        return $command;
    }

}
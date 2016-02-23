<?php

namespace Yaoi\Command;

use Yaoi\BaseClass;
use Yaoi\Command;
use Yaoi\Command\Option;
use Yaoi\Io\Request;
use Yaoi\Io\Response;
use Yaoi\Sql\Symbol;
use Yaoi\String\Utils;
use Yaoi\Undefined;

class Io extends BaseClass
{
    public $globalState;

    /** @var RequestMapperContract */
    protected $requestMapper;

    /** @var Response  */
    protected $response;

    /** @var  Command\Definition */
    protected $definition;

    /** @var  Option[] */
    protected $globalOptions = array();

    protected $commandStates = array();

    /** @var Command */
    public $command;

    /**
     * @return Command
     */
    public function getCommand()
    {
        return $this->command;
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
    private function makeDefinitionsTree(Definition $definition) {
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
     * @param Command $commandState
     */
    public function makeAnchor($commandState) {
        $commandState->commandClass;
        $commandClass = $commandState->commandClass;
        $commandClasses = array();
        while (isset($this->definitionTree[$commandClass])) {
            $commandClass = $this->definitionTree[$commandClass];
            $commandClasses []= $commandClass;
            $commandClass = $commandClass[0];
        }

        $properties = array();
        for ($i = count($commandClasses) - 1; $i >= 0; --$i) {
            /** @var Command|string $commandClass */
            list($commandClass, $optionName, $enumName) = $commandClasses[$i];
            $optionsArray = $commandClass::definition()->optionsArray();

            if (isset($this->commandStates[$commandClass])) {
                foreach ((array)$this->commandStates[$commandClass] as $name => $value) {
                    if (!isset($optionsArray[$name])) {
                        continue;
                    }

                    if ($name === $optionName) {
                        $properties[] = array($optionsArray[$name], $enumName);
                    }
                    else {
                        $properties[] = array($optionsArray[$name], $value);
                    }
                }
            }



            $commandClass = $commandState->commandClass;
            $optionsArray = $commandClass::definition()->optionsArray();

            foreach ((array)$commandState as $name => $value) {
                if (!isset($optionsArray[$name])) {
                    continue;
                }

                $properties[] = array($optionsArray[$name], $value);
            }

            //var_dump($properties);
            //var_dump('red beech', $commandClasses[$i], $commandClass, $optionName);
        }

        return $this->requestMapper->makeAnchor($properties);
    }


    /**
     * @param string|Command $commandClass
     * @return false|Command
     */
    public function getCommandState($commandClass) {
        if (isset($this->commandStates[$commandClass])) {
            return $this->commandStates[$commandClass];
        }
        return false;
    }

    protected function prepareCommand(Command\Definition $definition) {
        $commandClass = $definition->commandClass;

        /** @var Command $command */
        $command = new $commandClass;
        $command->setResponse($this->response);
        $command->setRequestMapper($this->requestMapper);
        $command->setIo($this);

        $commandOptions = $definition->optionsArray();
        $commandState = $this->requestMapper->readOptions($commandOptions);
        //var_dump($commandState);
        $this->commandStates[$definition->commandClass] = $commandState;

        foreach ($commandOptions as $option) {
            $this->globalOptions [$option->name]= $option; // todo consider managing overlapping options
            if (!isset($commandState->{$option->name})) {
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
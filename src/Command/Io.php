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

    public function __construct(Command\Definition $definition, RequestMapperContract $requestMapper, Response $response)
    {
        $this->requestMapper = $requestMapper;
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
        $command->setRequestMapper($this->requestMapper);

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



    public function renderUri(Command $commandState) {
        Command::cast($commandState)->createState();
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
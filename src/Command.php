<?php

namespace Yaoi;

use Phperf\Xhprof\Ui\Io;
use Yaoi\Command\Definition;
use Yaoi\Command\Option;
use Yaoi\Command\RunnerContract;
use Yaoi\Io\Response;
use Yaoi\String\Utils;


/**
 * @see Command is a unit of high level action.
 * Information may be passed to @see Command via a list of public properties described by list
 * of @see \Yaoi\Command\Option definitions.
 *
 * @see Definition contains list of
 * @see Option alongside with additional
 * @see Command information.
 *
 * @see RequestParser gets option values from
 * @see Request.
 *
 * @see Response provides methods for returning response data.
 *
 * @see ResponseRenderer generates output based on
 * @see Response data.
 *
 * @see \Yaoi\Command\Runner is setting up
 * @see Command with
 * @see RequestParser and invoking action.
 *
 *
 * @todo automated definition name by class name
 */


abstract class Command extends BaseClass implements Command\Contract
{
    private static $definitions = array();
    /**
     * @return static
     */
    public static function options() {
        return static::definition()->options;
    }

    /**
     * @return Option[]
     */
    public static function optionsArray() {
        return (array)static::definition()->options;
    }

    /**
     * @return Definition
     */
    public static function definition() {
        $className = get_called_class();
        $definition = &self::$definitions[$className];
        if (null !== $definition) {
            return $definition;
        }
        $definition = static::createDefinition();
        $definition->commandClass = $className;
        static::setUpDefinition($definition, $definition->options);
        $definition->setOptions($definition->options);
        return $definition;
    }

    protected static function createDefinition() {
        $definition = new Definition();
        $definition->options = new \stdClass();
        return $definition;
    }

    /** @var Response */
    protected $response;
    public function setResponse(Response $response) {
        $this->response = $response;
        return $this;
    }


    /**
     * @var \stdClass
     */
    private $currentState;

    /**
     * Router gets state and renders it to url/form/commandline, `base path` also required
     * @param bool $copyCurrent
     * @return static
     */
    public function createState($copyCurrent = true)
    {
        if ($copyCurrent) {
            if (null === $this->currentState) {
                $this->currentState = new \stdClass();
                foreach (self::optionsArray() as $name => $option) {
                    $this->currentState->$name = $this->$name;
                }
            }
            $state = clone $this->currentState;
        }
        else {
            $state = new \stdClass();
        }
        return $state;
    }


    public function importState(\stdClass $state)
    {
        foreach ((array)$state as $name => $value) {
            $this->$name = $value;
        }
        return $this;
    }


    /**
     * @var RunnerContract
     */
    public $runner;

    /**
     * @var Io
     */
    public $io;
}


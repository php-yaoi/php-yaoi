<?php

namespace Yaoi;

use Yaoi\Command\Definition;
use Yaoi\Command\Option;
use Yaoi\String\Utils;

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
        static::setUpDefinition($definition, $definition->options);
        $definition->setOptions($definition->options);
        return $definition;
    }

    protected static function createDefinition() {
        $definition = new Definition();
        $definition->options = new \stdClass();
        return $definition;
    }

    public function run() {
        $this->performAction();
    }

    abstract protected function performAction();
}


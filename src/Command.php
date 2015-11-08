<?php

namespace Yaoi;

use Yaoi\Command\Definition;
use Yaoi\Command\Option;
use Yaoi\String\Utils;

abstract class Command implements Command\Contract
{
    public function __construct() {
        $this->options = new \stdClass();
        $this->setUpDefinition($this->options);
        /**
         * @var string $name
         * @var Option $option
         */
        foreach ((array)$this->options as $name => $option) {
            if (empty($option->name)) {
                $option->name = Utils::fromCamelCase($name, '-');
            }

            $this->optionsByName[$option->name] = $option;
        }
    }


    private static $definitions = array();
    /**
     * @param bool|false $asArray
     * @return Option[]|\stdClass|Command|static
     */
    public static function options($asArray = false) {
        return static::definition()->options;
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
        $definition = new Definition();
        $options = new \stdClass();
        static::setUpDefinition($definition, $options);
        $definition->setOptions($options);
        return $definition;
    }

    public $optionsByName = array();
}
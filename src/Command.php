<?php

namespace Yaoi;

use Yaoi\Command\Option;
use Yaoi\String\Utils;

abstract class Command
{
    /**
     * @param static|\stdClass $options
     * @return void
     */
    abstract public function setUpOptions($options);
    abstract public function getDescription();
    abstract public function getName();
    abstract public function execute();


    /** @var  static|\stdClass */
    protected $options;

    public function __construct() {
        $this->options = new \stdClass();
        $this->setUpOptions($this->options);
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

    /**
     * @param bool|false $asArray
     * @return Option[]|\stdClass|Command|static
     */
    public function options($asArray = false) {
        return $asArray ? (array)$this->options : $this->options;
    }


    public $optionsByName = array();
}
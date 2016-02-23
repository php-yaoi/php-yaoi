<?php

namespace Yaoi\Command;

use Yaoi\BaseClass;
use Yaoi\Command;
use Yaoi\String\Utils;

class Definition extends BaseClass
{
    public $options;
    public $description;
    public $name;
    public $version;

    /** @var string|Command */
    public $commandClass;

    /** @var Definition[] */
    public $actions = array();

    public $allowUnexpectedOptions;

    /**
     * @return Option[]
     */
    public function optionsArray()
    {
        return (array)$this->options;
    }

    public function setOptions(\stdClass $options) {
        /**
         * @var string $name
         * @var Option $option
         */
        foreach ((array)$options as $name => $option) {
            $option->name = $name;
        }
        $this->options = $options;
        return $this;
    }

    public function getName() {
        if ($this->name) {
            return $this->name;
        }
        else {
            return substr($this->commandClass, strrpos($this->commandClass, '\\') + 1);
        }
    }
}
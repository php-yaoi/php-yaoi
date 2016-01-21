<?php

namespace Yaoi\Command;

use Yaoi\BaseClass;
use Yaoi\Command;

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

    /** @var Option[] */
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
}
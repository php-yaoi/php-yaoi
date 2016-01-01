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

    /** @var string */
    public $commandClass;

    /** @var Definition[] */
    public $actions = array();

    public $allowUnexpectedOptions;

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
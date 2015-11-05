<?php

namespace Yaoi\Cli;

class Option extends \Yaoi\Command\Option
{
    public $shortName;
    public function setShortName($shortName) {
        $this->shortName = $shortName;
        return $this;
    }

}
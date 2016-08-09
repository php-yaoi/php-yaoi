<?php

namespace Yaoi\Command;

use Yaoi\BaseClass;

class State extends BaseClass
{
    public $commandClass;
    private $properties = array();
    /** @var Io */
    private $io;

    public function setIo(Io $io = null)
    {
        $this->io = $io;
        return $this;
    }

    public function makeAnchor()
    {
        return $this->io->makeAnchor($this);
    }

    public function __get($name)
    {
        return isset($this->properties[$name]) ? $this->properties[$name] : null;
    }

    public function __set($name, $value)
    {
        $this->properties[$name] = $value;
    }

    public function hasProperty($name)
    {
        return array_key_exists($name, $this->properties);
    }


    public function export()
    {
        return $this->properties;
    }

}
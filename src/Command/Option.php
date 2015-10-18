<?php

namespace Yaoi\Command;

use Yaoi\BaseClass;

class Option extends BaseClass
{
    const TYPE_BOOL = 'bool';
    const TYPE_VALUE = 'value';
    const TYPE_ENUM = 'enum';

    public $values = array();

    public $name;
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function setEnum($values) {
        $this->type = self::TYPE_ENUM;
        $this->values = is_array($values) ? $values : func_get_args();
        return $this;
    }

    public function setType($type = self::TYPE_VALUE) {
        $this->type = $type;
        return $this;
    }

    public $description;
    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    public $required;
    public function setRequired($required = true) {
        $this->required = $required;
        return $this;
    }

    public $type = self::TYPE_BOOL;
}
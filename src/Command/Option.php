<?php

namespace Yaoi\Command;

use Yaoi\BaseClass;
use Yaoi\String\Utils;

class Option extends BaseClass
{
    const TYPE_BOOL = 'bool';
    const TYPE_VALUE = 'value';
    const TYPE_ENUM = 'enum';

    public $enumValues = array(); // TODO hide

    /** @var  array|string[] Enum values mapping */
    private $enumMap;
    public function setEnumMapper(\Closure $mapper = null) {
        if (null === $mapper) {
            $this->enumMap = null;
        }
        else {
            foreach ($this->enumValues as $name => $value) {
                $this->enumMap [$mapper($name)] = $value;
            }
        }
        return $this;
    }

    public $name;
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function setEnum($values) {
        $this->type = self::TYPE_ENUM;
        $values = is_array($values) ? $values : func_get_args();
        $this->enumValues = array_combine($values, $values);
        return $this;
    }

    public function setType($type = Option::TYPE_VALUE) {
        $this->type = $type;
        return $this;
    }

    public function addToEnum($value, $name = null) {
        $this->type = Option::TYPE_ENUM;
        if (null === $name) {
            if ($value instanceof Definition) {
                $name = $value->getName();
            }
            else {
                $name = $value;
            }
        }
        $this->enumValues[$name] = $value;
        return $this;
    }

    public $description;
    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    public $isRequired;
    public function setIsRequired($isRequired = true) {
        $this->isRequired = $isRequired;
        return $this;
    }

    public function validateFilterValue($value) {
        if ($this->type === self::TYPE_ENUM) {
            $enumValues = empty($this->enumMap) ? $this->enumValues : $this->enumMap;
            if (!isset($enumValues[$value])) {
                throw new Exception('Invalid value for `' . $this->name . '`: ' . $value .'. '
                    .'Allowed values: ' . implode(', ', array_keys($enumValues)) . '.', Exception::INVALID_VALUE);
            }
            else {
                return $enumValues[$value];
            }

        }
        elseif ($this->type === Option::TYPE_BOOL) {
            return (bool)$value;
        }
        elseif ($this->type === Option::TYPE_VALUE && empty($value) && $this->isRequired) {
            throw new Exception('Option `' . $this->name . '` can not be empty', Exception::OPTION_REQUIRED);
        }
        else {
            return $value;
        }
    }


    public function getPublicName() {
        return Utils::fromCamelCase($this->name, '_');
    }

    public $isVariadic = false;
    public function setIsVariadic($yes = true) {
        $this->isVariadic = $yes;
        if (self::TYPE_BOOL === $this->type) {
            $this->type = self::TYPE_VALUE;
        }
        return $this;
    }

    public $isUnnamed = false;
    public function setIsUnnamed($isUnnamed = true) {
        $this->isUnnamed = $isUnnamed;
        if ($isUnnamed && self::TYPE_BOOL === $this->type) {
            $this->type = self::TYPE_VALUE;
        }
        return $this;
    }


    public $type = self::TYPE_BOOL;
}
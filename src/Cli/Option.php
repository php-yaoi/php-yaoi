<?php

namespace Yaoi\Cli;

use Yaoi\String\Utils;

class Option extends \Yaoi\Command\Option
{
    public $shortName;
    public function setShortName($shortName) {
        $this->shortName = $shortName;
        return $this;
    }

    public function getName() {
        return Utils::fromCamelCase($this->name, '-');
    }

    public $isUnnamed = false;
    public function setIsUnnamed($isUnnamed = true) {
        $this->isUnnamed = $isUnnamed;
        if ($isUnnamed && self::TYPE_BOOL === $this->type) {
            $this->type = self::TYPE_VALUE;
        }
        return $this;
    }

    public function getUsage() {
        $usage = '';
        if (!$this->isUnnamed) {
            if ($this->shortName) {
                $usage = Runner::OPTION_SHORT . $this->shortName;
            }
            else {
                $usage = Runner::OPTION_NAME . $this->getName();
            }
        }

        $value = '';
        if ($this->type === self::TYPE_VALUE || $this->isUnnamed) {
            $value = $this->name;
        }
        elseif ($this->type === self::TYPE_ENUM) {
            $value = count($this->values) < 4
                ? implode('|', $this->values)
                : $this->name;
        }

        if ($value && $this->isVariadic) {
            $value .= '...';
        }

        if ($value) {
            if ($this->isRequired || !$this->isUnnamed) {
                $value = '<' . $value . '>';
            }
            else {
                $value = '[' . $value . ']';
            }
        }
        return $usage ? $usage . ' ' . $value : $value;
    }



    public $isVariadic = false;
    public function setIsVariadic($yes = true) {
        $this->isVariadic = $yes;
        return $this;
    }

}
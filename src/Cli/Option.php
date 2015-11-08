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
        return $this;
    }

    public function getUsage() {
        if ($this->shortName) {
            $usage = Command::OPTION_SHORT . $this->shortName;
        }
        else {
            $usage = Command::OPTION_NAME . $this->getName();
        }
        if ($this->type === self::TYPE_VALUE) {
            $usage .= ' <' . $this->name . ($this->isVariadic ? '...' : '') . '>';
        }
        elseif ($this->type === self::TYPE_ENUM) {
            $usage .= ' <' . implode('|', $this->values) . '>';
        }
        return $usage;
    }

    public $isVariadic = false;
    public function setIsVariadic($yes = true) {
        $this->isVariadic = $yes;
        return $this;
    }

}
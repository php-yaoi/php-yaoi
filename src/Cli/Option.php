<?php

namespace Yaoi\Cli;

use Yaoi\Cli\Command\RequestMapper;
use Yaoi\Cli\Command\Runner;

class Option extends \Yaoi\Command\Option
{
    public $shortName;
    public function setShortName($shortName) {
        $this->shortName = $shortName;
        return $this;
    }

    public function getPublicName() {
        return RequestMapper::getPublicName($this->name);
    }

    public $group = Runner::GROUP_DEFAULT;
    public function setGroup($group) {
        $this->group = $group;
        return $this;
    }

    public function getUsage() {
        $usage = '';
        if (!$this->isUnnamed) {
            if ($this->shortName) {
                $usage = Runner::OPTION_SHORT . $this->shortName;
            }
            else {
                $usage = Runner::OPTION_NAME . $this->getPublicName();
            }
        }

        $value = '';
        if ($this->type === self::TYPE_VALUE || $this->isUnnamed) {
            $value = $this->name;
        }
        elseif ($this->type === self::TYPE_ENUM) {
            $value = count($this->enumValues) < 4
                ? implode('|', $this->enumValues)
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

}
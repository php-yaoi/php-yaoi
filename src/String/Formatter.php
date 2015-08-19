<?php

namespace Yaoi\String;

use Yaoi\BaseClass;
use Yaoi\Debug;
use Yaoi\String\Quoter\Raw;

class Formatter extends BaseClass
{
    const DEFAULT_PLACEHOLDER = '?';
    private $placeholder = self::DEFAULT_PLACEHOLDER;

    private $statement;
    private $binds;

    public function __construct($statement, $binds = null) {
        $arguments = func_get_args();

        if (count($arguments) === 1 && is_array($arguments[0])) {
            $arguments = $arguments[0];
        }

        $this->statement = $arguments[0];

        $count = count($arguments);
        if ($count > 2) {
            array_shift($arguments);
            $this->binds = $arguments;
        } elseif (array_key_exists(1, $arguments)) {
            $this->binds = $arguments[1];
        }
    }

    private $quoter;
    public function setQuoter(Quoter $quoter = null) {
        $this->quoter = $quoter;
        return $this;
    }

    public function setPlaceholder($placeholder) {
        $this->placeholder = $placeholder;
        return $this;
    }

    static private $rawQuoter;
    public function build(Quoter $quoter = null) {
        if ($this->binds) {
            if ($quoter === null && $this->quoter !== null) {
                $quoter = $this->quoter;
            }

            if ($quoter === null) {
                if (null === self::$rawQuoter) {
                    self::$rawQuoter = new Raw();
                }
                $quoter = self::$rawQuoter;
            }

            $statement = $this->statement;

            $replace = array();
            $unnamed = true;

            // check binds array type
            $i = 0;
            foreach ($this->binds as $key => $value) {
                if ($unnamed && $key !== $i++) {
                    $unnamed = false;
                    break;
                }
            }

            if ($unnamed) {
                $pos = 0;
                $placeholderLength = strlen($this->placeholder);
                foreach ($this->binds as $value) {
                    $pos = strpos($statement, $this->placeholder, $pos);
                    if ($pos !== false) {
                        $value = $quoter->quote($value);
                        $statement = substr_replace($statement, $value, $pos, $placeholderLength);
                        $pos += strlen($value);
                    } else {
                        throw new Exception('Placeholder \'' . $this->placeholder . '\' not found ("' . $statement . '"), '
                            . Debug::varBrief($this->binds), Exception::PLACEHOLDER_NOT_FOUND);
                    }
                }

                if (strpos($statement, $this->placeholder, $pos) !== false) {
                    throw new Exception('Redundant placeholder: "' . $statement . '"',
                        Exception::PLACEHOLDER_REDUNDANT);
                }

                $result = $statement;
            } else {
                foreach ($this->binds as $key => $value) {
                    $replace [':' . $key] = $quoter->quote($value);
                }
                $result = strtr($statement, $replace);
            }
        } else {
            $result = $this->statement;
        }

        return $result;
    }

    public function __toString() {
        try {
            return $this->build();
        }
        catch (\Exception $e) {
            return '#ERROR: (' .  $e->getCode() . ') ' . $e->getMessage();
        }
    }

    public function setBinds($binds) {
        $arguments = func_get_args();
        if (count($arguments) === 1 && is_array($arguments)) {
            $arguments = $arguments[0];
        }
        $this->binds = $arguments;
        return $this;
    }


}
<?php

namespace Yaoi\Database\Definition;

use Yaoi\BaseClass;
use Yaoi\Sql\Symbol;

class Column extends BaseClass
{

    const AUTO_ID = 1;
    const INTEGER = 2;
    const UNSIGNED = 4;
    const FLOAT = 8;
    const STRING = 16;

    const SIZE_1B = 32;
    const SIZE_2B = 64;
    const SIZE_3B = 128;
    const SIZE_4B = 256;
    const SIZE_8B = 512;

    const TIMESTAMP = 1024;
    const NOT_NULL = 2048;

    const AUTO_TYPE = 4096;

    const USE_PHP_DATETIME = 8192;

    public $flags;
    public function __construct($flags = self::STRING)
    {
        if ($flags === self::AUTO_ID) {
            $flags += self::INTEGER;
            $flags += self::NOT_NULL;
        }

        $this->flags = $flags;
    }

    public function setFlag($flag, $add = true) {
        $flagSet = $this->flags & $flag;
        if ($add && !$flagSet) {
            $this->flags += $flag;
        }
        elseif (!$add && $flagSet) {
            $this->flags -= $flag;
        }
        return $this;
    }


    private $default = false; // todo properly deprecate false value in favour of null
    public function setDefault($value) {

        $this->default = $value;
        return $this;
    }

    /**
     * @return bool|null
     * @todo move custom logic to utility
     */
    public function getDefault() {
        if (is_string($this->default)) {
            if ($this->flags & Column::INTEGER) {
                $this->default = (int)$this->default;
            }
            elseif ($this->flags & Column::FLOAT) {
                $this->default = (float)$this->default;
            }
        }
        elseif ($this->flags & Column::STRING) {
            if (is_int($this->default) || is_float($this->default)) {
                $this->default = (string)$this->default;
            }
        }
        return $this->default;
    }

    public $stringLength;
    public $stringFixed;
    public function setStringLength($length, $fixed = false) {
        $this->stringLength = $length;
        $this->stringFixed = $fixed;
        return $this;
    }

    public $isUnique;
    public function setUnique($yes = true) {
        $this->isUnique = $yes;
        return $this;
    }

    public $isIndexed;
    public function setIndexed($yes = true) {
        $this->isIndexed = $yes;
        return $this;
    }


    /**
     * Name of mapped class property, usually in camelCase
     * @var string
     */
    public $propertyName;

    /**
     * Name of database table column, in underscore_lowercase
     * @var
     */
    public $schemaName;

    /** @var  Table */
    public $table;

    public static function castField($value, $columnFlags, $import = true)
    {
        if (!($columnFlags & self::NOT_NULL) && $value === null) {
            return null;
        }

        if (is_object($value) && !$value instanceof \DateTime) {
            $value = (string)$value;
        }

        if ($columnFlags !== self::AUTO_TYPE) {
            switch (true) {
                case self::FLOAT & $columnFlags:
                    $value = (float)$value;
                    break;
                case self::INTEGER & $columnFlags && self::USE_PHP_DATETIME & $columnFlags:
                    if ($import) {
                        $datetime = new \DateTime();
                        $datetime->setTimestamp($value);
                        $value = $datetime;
                    } elseif ($value instanceof \DateTime) {
                        $value = $value->getTimestamp();
                    }
                    break;
                case self::INTEGER & $columnFlags:
                    $value = (int)$value;
                    break;
                case self::STRING & $columnFlags:
                    $value = (string)$value;
                    break;
                case self::TIMESTAMP & $columnFlags && self::USE_PHP_DATETIME & $columnFlags:
                    if ($import) {
                        $value = new \DateTime($value);
                    } elseif ($value instanceof \DateTime) {
                        $value = $value->format("Y-m-d H:i:s");
                    }
                    break;
            }
        }

        return $value;
    }


    public function getTypeString() {
        return $this->table->database()->getUtility()->getColumnTypeString($this);
    }
}
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


    private $default = false;
    public function setDefault($value) {
        $this->default = $value;
        return $this;
    }

    public function getDefault() {
        if (false === $this->default && !($this->flags & self::NOT_NULL) && !($this->flags & self::TIMESTAMP)) {
            return null;
        }
        else {
            return $this->default;
        }
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

    public static function castField($value, $columnType)
    {
        if (is_object($value)) {
            $value = (string)$value;
        }

        if ($columnType !== self::AUTO_TYPE) {
            switch (true) {
                case self::FLOAT & $columnType:
                    $value = (float)$value;
                    break;
                case self::INTEGER & $columnType:
                    $value = (int)$value;
                    break;
                case self::STRING & $columnType:
                    $value = (string)$value;
            }
        }

        return $value;
    }


    private $foreignKey;
    public function setForeignKey(Column $column, $onUpdate = ForeignKey::NO_ACTION, $onDelete = ForeignKey::NO_ACTION) {
        $this->foreignKey = new ForeignKey(array($this), array($column), $onUpdate, $onDelete);
        return $this;
    }

    public function getForeignKey() {
        return $this->foreignKey;
    }
}
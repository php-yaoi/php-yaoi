<?php

namespace Yaoi\Rows;
use ArrayIterator;
use IteratorIterator;

class Processor extends IteratorIterator
{
    protected $rows = array();
    private $skipFields = array();

    public function skipField($field)
    {
        $this->skipFields[$field] = $field;
        return $this;
    }

    private $changeKeys = array();

    public function changeKey($fieldFrom, $fieldTo)
    {
        $this->changeKeys[$fieldFrom] = $fieldTo;
        return $this;
    }


    private $combineFields = array();

    public function combine($fieldKey, $fieldValue)
    {
        $this->combineFields[$fieldKey] = $fieldValue;
        return $this;
    }

    private $combineOffset = array();

    public function combineOffset($offsetKey, $offsetValue)
    {
        $this->combineOffset [$offsetKey] = $offsetValue;
        return $this;
    }

    public function __construct(&$rows = null)
    {
        if (is_array($rows)) {
            parent::__construct(new ArrayIterator($rows));
        } else {
            parent::__construct($rows);
        }
    }

    public function current()
    {
        $row = parent::current();
        if ($this->skipFields) {
            foreach ($this->skipFields as $field) {
                if (isset($row[$field])) {
                    unset($row[$field]);
                }
            }
        }

        $keys = null;

        if ($this->combineOffset) {
            foreach ($this->combineOffset as $offsetKey => $offsetValue) {
                $keys = array_keys($row);
                $row[$row[$keys[$offsetKey]]] = $row[$keys[$offsetValue]];
                unset($row[$keys[$offsetKey]]);
                unset($row[$keys[$offsetValue]]);
            }
        }

        if ($this->combineFields) {
            foreach ($this->combineFields as $fieldKey => $fieldValue) {
                $row[$row[$fieldKey]] = $row[$fieldValue];
                unset($row[$fieldKey]);
                unset($row[$fieldValue]);
            }
        }

        if ($this->changeKeys) {
            if (!$keys) {
                $keys = array_keys($row);
            }
            foreach ($keys as &$key) {
                if (isset($this->changeKeys[$key])) {
                    $key = $this->changeKeys[$key];
                }
            }
            unset($key);
            $row = array_combine($keys, $row);
        }

        return $row;
    }


    static function create($rows)
    {
        return new static($rows);
    }


    public function exportArray()
    {
        $result = array();
        foreach ($this as $row) {
            $result [] = $row;
        }
        return $result;
    }

} 
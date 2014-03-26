<?php

class Rows_Processor extends ArrayIterator {
    protected $rows = array();
    private $skipFields = array();
    public function skipField($field) {
        $this->skipFields[$field] = $field;
        return $this;
    }

    private $changeKeys = array();
    public function changeKey($fieldFrom, $fieldTo) {
        $this->changeKeys[$fieldFrom] = $fieldTo;
        return $this;
    }


    private $combineFields = array();
    public function combine($fieldKey, $fieldValue) {
        $this->combineFields[$fieldKey] = $fieldValue;
        return $this;
    }



    public function __construct(&$rows = null) {
        parent::__construct($rows);
    }

    public function current() {
        $row = parent::current();
        if ($this->skipFields) {
            foreach ($this->skipFields as $field) {
                if (isset($row[$field])) {
                    unset($row[$field]);
                }
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
            $keys = array_keys($row);
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



    static function create($rows) {
        return new static($rows);
    }



} 
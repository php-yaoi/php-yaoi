<?php

class MappableBase extends Base_Class implements Mappable {
    private $mappedProperties = array();

    /**
     * @param array $row
     * @param MappableBase $object
     * @return static
     */
    static public function fromArray(array $row, MappableBase $object = null) {
        if (is_null($object)) {
            $object = new static;
        }

        foreach ($row as $key => $value) {
            $object->mappedProperties[$key] = $key;
            $object->$key = $value;
        }
        return $object;
    }


    /**
     * @var SplObjectStorage
     */
    static private $activeIterators;
    public static function __init() {
        self::$activeIterators = new SplObjectStorage();
    }

    /**
     * @param Iterator $rows
     * @return bool|static
     */
    static public function iterate(Iterator $rows) {
        if (!self::$activeIterators->contains($rows)) {
            self::$activeIterators->attach($rows);
            $rows->rewind();
        }

        if (!$rows->valid()) {
            self::$activeIterators->detach($rows);
            return false;
        }

        $row = $rows->current();
        $rows->next();

        return static::fromArray($row);
    }


    public function toArray() {
        $result = array();
        foreach ($this->mappedProperties as $key) {
            $result[$key] = $this->$key;
        }
        return $result;
    }
}
MappableBase::__init();


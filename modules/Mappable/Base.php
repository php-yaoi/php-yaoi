<?php

use Yaoi\BaseClass;
use Yaoi\Mappable\Contract;
use Yaoi\Mappable\Iterator;

abstract class Base extends BaseClass implements Contract {
    protected static $mappedProperties = array();
    protected $fromProperties;

    /**
     * @param array $row
     * @param Base $object
     * @return static
     */
    static public function fromArray(array $row, $object = null, $source = null) {
        if (is_null($object)) {
            $object = new static;
        }

        $object->fromProperties = array();

        foreach (static::$mappedProperties as $property) {
            if (array_key_exists($property, $row)) {
                $object->fromProperties []= $property;
                $object->$property = $row[$property];
            }
        }

        return $object;
    }


    /**
     * @param $rows
     * @return static[]
     */
    static public function iterator(&$rows) {
        return new Iterator($rows, get_called_class());
    }


    public function toArray($skipNotSetProperties = false) {
        $result = array();
        if (null !== $this->fromProperties) {
            foreach ($this->fromProperties as $property) {
                $result [$property] = $this->$property;
            }
        }
        else {
            foreach (static::$mappedProperties as $key) {
                $result[$key] = $this->$key;
            }
        }
        return $result;
    }
}


<?php

abstract class Mappable_Base extends Base_Class implements Mappable {
    protected static $mappedProperties = array();
    protected $fromProperties;

    /**
     * @param array $row
     * @param Mappable_Base $object
     * @return static
     */
    static public function fromArray(array $row, $object = null) {
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
        return new Mappable_Iterator($rows, get_called_class());
    }


    public function toArray() {
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


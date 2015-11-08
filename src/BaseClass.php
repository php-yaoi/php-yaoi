<?php

namespace Yaoi;
abstract class BaseClass
{
    /**
     * @return static
     */
    static function create()
    {
        $args = func_get_args();
        switch (count($args)) {
            case 0:
                return new static();
            case 1:
                return new static($args[0]);
            case 2:
                return new static($args[0], $args[1]);
            case 3:
                return new static($args[0], $args[1], $args[2]);
            case 4:
                return new static($args[0], $args[1], $args[2], $args[3]);
            case 5:
                return new static($args[0], $args[1], $args[2], $args[3], $args[4]);
        }
        return new static;
    }


    /**
     * @return static
     */
    public function copy()
    {
        return clone $this;
    }


    public static function className()
    {
        return get_called_class();
    }


    public static function __set_state(array $properties) {
        $instance = new static;
        foreach ($properties as $property => $value) {
            $instance->$property = $value;
        }
        return $instance;
    }


    /**
     * @param BaseClass $object
     * @return static
     * @throws \Exception
     */
    public static function cast(BaseClass $object) {
        if ($object instanceof static) {
            return $object;
        }
        $newObject = new static();
        // todo check performance of reflection and cache meta if needed
        $ref = new \ReflectionClass($object);
        $properties = $ref->getProperties(
            \ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED
        );
        foreach ($properties as $property) {
            $propertyName = $property->name;
            $newObject->$propertyName = $object->$propertyName;
        }
        return $newObject;
    }

}
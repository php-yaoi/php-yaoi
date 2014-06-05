<?php

class Mappable_Iterator extends IteratorIterator {

    private $class;

    public function __construct(&$rows = null, $class) {
        $this->class = $class;
        if (is_array($rows)) {
            parent::__construct(new ArrayIterator($rows));
        }
        else {
            parent::__construct($rows);
        }
    }


    public function current() {
        $class = $this->class;
        return $class::fromArray(parent::current());
    }
}
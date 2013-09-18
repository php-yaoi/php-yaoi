<?php

class Base_Collection extends Base_Class {
    public $items = array();

    public function add($item) {
        $this->items []= $item;
    }
}
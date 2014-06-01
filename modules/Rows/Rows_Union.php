<?php

class Rows_Union {
    protected $data = array();



    public function hasChildren() {
        return is_array($this->current());
    }
}



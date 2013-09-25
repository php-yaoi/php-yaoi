<?php

interface Mock_DataSet {
    public function add($key, $value);
    public function get($key = null);
    public function export();
    public function import($data);
}
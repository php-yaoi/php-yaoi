<?php

interface Mock_DataSetPlay extends Mock_DataSet {
    public function __construct(Storage_KeyValue $data);
    public function get($key = null, $section = null);
    public function reset();
}
<?php

interface Mock_DataSet {
    public function add($key, $value);
    public function get($key = null);
    public function reset();
    public function capture(Storage_KeyValue $data);
    public function play(Storage_KeyValue $data);
    public function isPlayActive();
    public function isCaptureActive();
}
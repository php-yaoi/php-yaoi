<?php

interface Storage_KeyValue {
    public function get($key);
    public function set($key, $value);
    public function remove($key);
    public function deleteAll();
}
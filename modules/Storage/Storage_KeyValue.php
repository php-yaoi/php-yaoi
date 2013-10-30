<?php

interface Storage_KeyValue {
    public function get($key);
    public function set($key, $value);
    public function delete($key);
    public function deleteAll();
}
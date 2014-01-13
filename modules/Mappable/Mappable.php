<?php
interface Mappable {
    static function fromArray(array $row, $object = null);
    public function toArray();
}
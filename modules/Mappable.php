<?php
interface Mappable {
    static function fromArray(array $row, MappableBase $object = null);
    public function toArray();
}
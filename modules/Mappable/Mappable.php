<?php
interface Mappable {
    static function fromArray(array $row, $object = null, $source = null);
    public function toArray($skipNotSetProperties = false);
}
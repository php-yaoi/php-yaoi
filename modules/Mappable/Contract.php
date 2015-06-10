<?php
namespace Yaoi\Mappable;
interface Contract
{
    static function fromArray(array $row, $object = null, $source = null);

    public function toArray($skipNotSetProperties = false);
}
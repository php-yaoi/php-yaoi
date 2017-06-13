<?php
namespace YaoiTests\Helper\Mappable;


class MappableTest1 extends \Yaoi\Mappable\Base
{
    public $one;
    public $two;
    public $three;

    public $nonMapped;
    protected static $mappedProperties = array(
        'three',
        'two',
        'one',
    );
}
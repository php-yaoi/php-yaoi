<?php
use Yaoi\Test\PHPUnit\TestCase;



class MappableTest extends TestCase {
    public function testMappable() {
        $a = array(
            'zero' => 0,
            'one' => 1,
            // no 'two'
            'three' => 3,
            'four' => 4,
        );

        $m = MappableTest1::fromArray($a)->toArray();
        $this->assertSame(array (
                'three' => 3,
                'one' => 1,
            ), $m);


        $a = array(
            'zero' => 0,
            'one' => 1,
            'two' => 2,
            'three' => 3,
            'four' => 4,
        );

        $m = MappableTest1::fromArray($a)->toArray();
        $this->assertSame(array (
            'three' => 3,
            'two' => 2,
            'one' => 1,
        ), $m);


        $m = new MappableTest1;
        $m->nonMapped = 123;
        $m->one = 11;
        $this->assertSame(array (
            'three' => NULL,
            'two' => NULL,
            'one' => 11,
        ), $m->toArray());

    }


    public function testIterator() {
        $res = array(
            array('one' => 1, 'two' => 2, 'three' => 3),
            array('one' => 11, 'two' => 22, 'three' => 33),
            array('one' => 111, 'two' => 222, 'three' => 333),
        );


        $expected = array(
            array(1,2,3),
            array(11,22,33),
            array(111,222,333),
        );

        $test1 = array();
        foreach (MappableTest1::iterator($res) as $r) {
            $test1 []= array($r->one, $r->two, $r->three);
        }
        $this->assertSame($expected, $test1);


        $test1 = array();
        foreach (MappableTest1::iterator(new ArrayIterator($res)) as $r) {
            $test1 []= array($r->one, $r->two, $r->three);
        }
        $this->assertSame($expected, $test1);

    }
}


class MappableTest1 extends \Yaoi\Mappable\Base {
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
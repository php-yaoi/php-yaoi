<?php
use Yaoi\BaseClass;
use Yaoi\Test\PHPUnit\TestCase;

class BaseTest1 extends BaseClass {
    public $amount;

    public function __construct($var1, $var2, $var3, $var4, $var5) {
        $this->amount = $var1 + $var2 + $var3 + $var4 + $var5;
    }
}


class BaseTest extends TestCase  {

    public function testBaseCreate() {
        $test = new BaseTest1(1, 2, 3, 4, 5);

        $this->assertSame($test->amount, BaseTest1::create(1, 2, 3, 4, 5)->amount);
    }

} 
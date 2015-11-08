<?php

namespace YaoiTests\PHPUnit\Base;


use Yaoi\Test\PHPUnit\TestCase;
use YaoiTests\Helper\Base\Alpha;
use YaoiTests\Helper\Base\Beta;
use YaoiTests\Helper\Base\Delta;
use YaoiTests\Helper\Base\Gamma;

class CastTest extends TestCase
{
    /**
     * @example
     * @see BaseClass::cast
     */
    public function testCastSubclass() {
        $a = new Alpha();
        $b = Beta::cast($a);
        $this->assertInstanceOf(Beta::className(), $b);
        $this->assertSame($a->publicPropertyAlpha, $b->publicPropertyAlpha);
        $this->assertSame($a->getProtectedPropertyAlpha(), $b->getProtectedPropertyAlpha());

        $g = new Gamma();
        $g->publicPropertyAlpha = 'ga0';
        $b = Beta::cast($g);
        $this->assertInstanceOf(Beta::className(), $b);
        $this->assertSame($g->publicPropertyAlpha, $b->publicPropertyAlpha);
        $this->assertSame($g->getProtectedPropertyAlpha(), $b->getProtectedPropertyAlpha());

        $d = new Delta();
        $b = Beta::cast($d);
        $this->assertInstanceOf(Beta::className(), $b);
    }
}
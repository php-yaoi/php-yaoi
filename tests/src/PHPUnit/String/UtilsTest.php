<?php

namespace YaoiTests\PHPUnit\String;

use Yaoi\String\Utils;
use Yaoi\Test\PHPUnit\TestCase;


class UtilsTest extends TestCase
{
    public function testCamelCase()
    {
        $this->assertSame('lala-lala', Utils::fromCamelCase('LalaLala', '-'));
        $this->assertSame('data_abbr', Utils::fromCamelCase('DataABBR'));
        $this->assertSame('eshop', Utils::fromCamelCase('EShop'));
        $this->assertSame('LalaLala', Utils::toCamelCase('lala-lala', '-'));
    }


    public function testStartsEnds()
    {
        $this->assertSame(true, Utils::starts('the string', 'the'));
        $this->assertSame(false, Utils::starts('the string', 'string'));
        $this->assertSame(false, Utils::ends('the string', 'the'));
        $this->assertSame(true, Utils::ends('the string', 'string'));
    }


    public function testStrPos()
    {
        $this->assertSame(4, Utils::strPos('the string', 's'));

        $this->assertSame(3, Utils::strPos("one\ttwo one", array(' ', "\t")));
        $this->assertSame(3, Utils::strPos("one two one", array(' ', "\t")));

        $this->assertSame(0, Utils::strPos("one two one", array('one', 'two')));
        $this->assertSame(4, Utils::strPos("one two one", array('one', 'two'), 1));

        $this->assertSame(8, Utils::strPos("one two one", array('one', 'two'), 1, true));
        $this->assertSame(8, Utils::strPos("one two one", array('ONE', 'TWO'), 1, true, true));

        $this->assertSame(false, Utils::strPos("one two one", 'TWO', 0, true));
        $this->assertSame(4, Utils::strPos("one two one", 'TWO', 0, true, true));
        $this->assertSame('TWO', Utils::$strPosLastFound);

        $this->assertSame(2, Utils::strPos(1234, 34));

    }

}
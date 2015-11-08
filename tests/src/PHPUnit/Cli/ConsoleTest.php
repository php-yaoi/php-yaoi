<?php

namespace YaoiTests\PHPUnit\Cli;


use Yaoi\Cli\Console;
use Yaoi\Cli\View\Table;
use Yaoi\Console\Colored;
use Yaoi\Test\PHPUnit\TestCase;
use Yaoi\View\Semantic\Rows;

class ConsoleTest extends TestCase
{

    public function testColors() {
        $con = Console::create();

        $ref = new \ReflectionClass(Console::className());

        ob_start();
        foreach ($ref->getConstants() as $key => $value) {
            $con->set($value)->printLine($key)->set();
        }
        $out = ob_get_clean();
        $this->assertSame("\x1B" . '[0mRESET' . "\r\n"
            . "\x1B" . '[m' . "\x1B" . '[1mBOLD' . "\r\n"
            . "\x1B" . '[m' . "\x1B" . '[30mFG_BLACK' . "\r\n"
            . "\x1B" . '[m' . "\x1B" . '[31mFG_RED' . "\r\n"
            . "\x1B" . '[m' . "\x1B" . '[32mFG_GREEN' . "\r\n"
            . "\x1B" . '[m' . "\x1B" . '[33mFG_BROWN' . "\r\n"
            . "\x1B" . '[m' . "\x1B" . '[34mFG_BLUE' . "\r\n"
            . "\x1B" . '[m' . "\x1B" . '[35mFG_MAGENTA' . "\r\n"
            . "\x1B" . '[m' . "\x1B" . '[36mFG_CYAN' . "\r\n"
            . "\x1B" . '[m' . "\x1B" . '[37mFG_WHITE' . "\r\n"
            . "\x1B" . '[m' . "\x1B" . '[40mBG_BLACK' . "\r\n"
            . "\x1B" . '[m' . "\x1B" . '[41mBG_RED' . "\r\n"
            . "\x1B" . '[m' . "\x1B" . '[42mBG_GREEN' . "\r\n"
            . "\x1B" . '[m' . "\x1B" . '[43mBG_BROWN' . "\r\n"
            . "\x1B" . '[m' . "\x1B" . '[44mBG_BLUE' . "\r\n"
            . "\x1B" . '[m' . "\x1B" . '[45mBG_MAGENTA' . "\r\n"
            . "\x1B" . '[m' . "\x1B" . '[46mBG_CYAN' . "\r\n"
            . "\x1B" . '[m' . "\x1B" . '[47mBG_WHITE' . "\r\n"
            . "\x1B" . '[m' . "\x1B" . '[49mBG_DEFAULT' . "\r\n"
            . "\x1B" . '[m',
            $out);
        //echo $this->varExportString($out);
    }

    public function testReturnCaret() {
        $con = Console::getInstance();
        ob_start();
        for ($i = 0; $i < 100; ++$i) {
            $con->returnCaret()->printF($i);
        }
        $out = ob_get_clean();
        $this->assertSame("\r" . implode("\r", range(0, 99)), $out);
    }

    public function testTable() {
        $rows = new \ArrayIterator(array(
            array(1, 2, 3),
            array('one', 'two', 'three'),
            array('alpha', 'beta', 'gamma'),
        ));

        $result = Table::create($rows)->__toString();
        $this->assertSame(
              '1       2      3    ' . "\r\n"
            . 'one     two    three' . "\r\n"
            . 'alpha   beta   gamma' . "\r\n"
        , $result);

    }

}
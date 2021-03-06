<?php

namespace YaoiTests\PHPUnit\Cli;


use Yaoi\Cli\Console;
use Yaoi\Cli\View\Table;
use Yaoi\Test\PHPUnit\TestCase;

class ConsoleTest extends TestCase
{

    public function testColors() {
        $con = new Console();
        $con->forceColors = true;

        $ref = new \ReflectionClass(Console::className());

        ob_start();
        foreach ($ref->getConstants() as $key => $value) {
            $con->set($value)->printLine($key)->set();
        }
        $out = ob_get_clean();
        $this->assertSame('RESET' . PHP_EOL
            . "\x1B" . '[m' . "\x1B" . '[1mBOLD' . PHP_EOL
            . "\x1B" . '[m' . "\x1B" . '[30mFG_BLACK' . PHP_EOL
            . "\x1B" . '[m' . "\x1B" . '[31mFG_RED' . PHP_EOL
            . "\x1B" . '[m' . "\x1B" . '[32mFG_GREEN' . PHP_EOL
            . "\x1B" . '[m' . "\x1B" . '[33mFG_BROWN' . PHP_EOL
            . "\x1B" . '[m' . "\x1B" . '[34mFG_BLUE' . PHP_EOL
            . "\x1B" . '[m' . "\x1B" . '[35mFG_MAGENTA' . PHP_EOL
            . "\x1B" . '[m' . "\x1B" . '[36mFG_CYAN' . PHP_EOL
            . "\x1B" . '[m' . "\x1B" . '[37mFG_WHITE' . PHP_EOL
            . "\x1B" . '[m' . "\x1B" . '[40mBG_BLACK' . PHP_EOL
            . "\x1B" . '[m' . "\x1B" . '[41mBG_RED' . PHP_EOL
            . "\x1B" . '[m' . "\x1B" . '[42mBG_GREEN' . PHP_EOL
            . "\x1B" . '[m' . "\x1B" . '[43mBG_BROWN' . PHP_EOL
            . "\x1B" . '[m' . "\x1B" . '[44mBG_BLUE' . PHP_EOL
            . "\x1B" . '[m' . "\x1B" . '[45mBG_MAGENTA' . PHP_EOL
            . "\x1B" . '[m' . "\x1B" . '[46mBG_CYAN' . PHP_EOL
            . "\x1B" . '[m' . "\x1B" . '[47mBG_WHITE' . PHP_EOL
            . "\x1B" . '[m' . "\x1B" . '[49mBG_DEFAULT' . PHP_EOL
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

        Console::getInstance()->set();

        $result = Table::create($rows)->__toString();
        $this->assertSame(
              '1       2      3    ' . PHP_EOL
            . 'one     two    three' . PHP_EOL
            . 'alpha   beta   gamma' . PHP_EOL
        , $result);

    }

}
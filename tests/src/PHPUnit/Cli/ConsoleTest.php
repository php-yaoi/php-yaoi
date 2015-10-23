<?php

namespace YaoiTests\PHPUnit\Cli;


use Yaoi\Cli\Console;
use Yaoi\Console\Colored;
use Yaoi\Test\PHPUnit\TestCase;

class ConsoleTest extends TestCase
{

    public function testOne() {
        $con = Console::create();
        $con->set(Console::FG_BROWN);
        echo 'hooy';
        $con->set(Console::FG_BLACK, Console::BOLD);
        echo 'hooy';
        $con->set(Console::FG_WHITE, Console::BG_WHITE);
        echo 'hooy';
        $con->set();

        echo Colored::get('hooy', Colored::FG_GREEN);
        echo Colored::get('hooy', Colored::FG_LIGHT_GREEN);

        $ref = new \ReflectionClass(Console::className());
        print_r($ref->getConstants());

        foreach ($ref->getConstants() as $key => $value) {
            $con->set($value);
            echo $key, PHP_EOL;
            $con->set();
        }

    }
}
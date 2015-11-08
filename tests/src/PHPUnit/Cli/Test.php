<?php

namespace YaoiTests\PHPUnit\Cli;

use Yaoi\Cli\Router;
use Yaoi\Request;
use Yaoi\Test\PHPUnit\TestCase;
use YaoiTests\Helper\Command\CommandTheFirst;
use YaoiTests\Helper\Command\CommandTwo;

class Test extends TestCase
{

    public function testHelp2() {
        ob_start();
        CommandTheFirst::help();
        $result = ob_get_clean();
        //echo $this->varExportString($result);
        $this->assertSame("\x1B" . '[34;1mthe-first' . PHP_EOL
            . "\x1B" . '[mThis is a command one for doing nothing' . PHP_EOL
            . PHP_EOL
            . "\x1B" . '[34mUsage: ' . PHP_EOL
            . "\x1B" . '[m    <action> [argumentA] [argumentB...] -d <optionD> --some-enum <one|two|three>' . PHP_EOL
            . PHP_EOL
            . "\x1B" . '[34mOptions: ' . PHP_EOL
            . "\x1B" . '[m   <action>                      Main action                          ' . PHP_EOL
            . '   [argumentA]                   Bee description follows              ' . PHP_EOL
            . '   [argumentB...]                This is a variadic argument          ' . PHP_EOL
            . '   --option-c                    Some option for the C                ' . PHP_EOL
            . '   -d <optionD>                  Short name option with required value' . PHP_EOL
            . '   --some-enum <one|two|three>   Enumerated option to set up something' . PHP_EOL
            . '   ' . PHP_EOL,
            $result);
    }

    public function testHelp() {
        return;
        $router = new Router();
        $router->addCommand(new CommandTheFirst());
        $router->addCommand(new CommandTwo());

        $router->route($this->getRequest(array('./cli', 'help', 'one')));

        //$router->route($this->getRequest(array('./cli', 'help')));
    }


    private function getRequest(array $argv) {
        $request = Request::__set_state(array(
            'baseUrl' => '/',
            'get' =>
                array(),
            'post' =>
                array(),
            'request' =>
                array(),
            'cookie' =>
                array(),
            'server' =>
                Request\Server::__set_state(array(
                    'SCRIPT_NAME' => './cli',
                    'SCRIPT_FILENAME' => './cli',
                    'PHP_SELF' => './cli',
                    'argv' => $argv,
                    'argc' => count($argv),
                )),
            'isCli' => true,
        ));
        return $request;
    }
}
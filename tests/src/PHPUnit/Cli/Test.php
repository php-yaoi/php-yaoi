<?php

namespace YaoiTests\PHPUnit\Cli;

use Yaoi\Cli\Router;
use Yaoi\Request;
use Yaoi\Test\PHPUnit\TestCase;
use YaoiTests\Helper\Command\CommandTheFirst;
use YaoiTests\Helper\Command\CommandTwo;

class Test extends TestCase
{
    public function testHelp() {
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
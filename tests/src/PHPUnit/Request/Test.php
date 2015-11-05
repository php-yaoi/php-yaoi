<?php

namespace YaoiTests\PHPUnit\Request;

use Yaoi\Request;
use Yaoi\Test\PHPUnit\TestCase;

class Test extends TestCase
{
    public function testCreateAuto() {
        $request = Request::createAuto();
        $this->assertTrue($request->isCli());
        $this->assertSame(null, $request->path());
    }

    public function testAccessors() {
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
                    'argv' => array('one', 'two', 'three'),
                    'argc' => 3,
                )),
            'isCli' => true,
        ));

    }

}
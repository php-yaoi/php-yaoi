<?php

namespace YaoiTests\PHPUnit\Command;

use Yaoi\Cli\Response;
use Yaoi\Command\Io;
use Yaoi\Command\Web\RequestMapper;
use Yaoi\Io\Request;
use Yaoi\Test\PHPUnit\TestCase;
use YaoiTests\Helper\Command\TestCommandNested;
use YaoiTests\Helper\Command\TestCommandOne;

class StateTest extends TestCase
{
    private function getIo()
    {
        $request = new Request();
        $request->server()->REQUEST_URI = '/the-first/get/';
        $request->setParam(Request::REQUEST, 'some_enum', 'one')
            ->setParam(Request::REQUEST, 'option_d', 15);


        $requestMapper = new RequestMapper($request);
        $response = new Response();

        $io = new Io(TestCommandNested::definition(), $requestMapper, $response);
        return $io;
    }

    public function testCreateState()
    {
        $commandState = TestCommandOne::createState($this->getIo());
        $this->assertSame(array (
            'action' => 'get',
            'optionD' => 15,
            'someEnum' => 'one',
            'commandClass' => 'YaoiTests\\Helper\\Command\\TestCommandOne',
        ), (array)$commandState);
    }

    public function testGetCommandState()
    {
        $io = $this->getIo();
        $state = $io->getCommandState(TestCommandOne::className());
        $this->assertSame(array (
            'action' => 'get',
            //'optionC' => false,
            'optionD' =>
                array (
                    0 => 15,
                ),
            'someEnum' => 'one',
        ), (array)$state);
    }
}
<?php

namespace YaoiTests\PHPUnit\Command;


use Yaoi\Cli\Response;
use Yaoi\Command\Io;
use Yaoi\Command\Web\RequestMapperWithPath;
use Yaoi\Io\Request;
use Yaoi\Test\PHPUnit\TestCase;
use YaoiTests\Helper\Command\TestCommandNested;

class NestedTest extends TestCase
{
    public function testNestedWeb() {
        $request = new Request();
        $request->server()->REQUEST_URI = '/one/two/three?param1=3&param2=4';

        $requestMapper = new RequestMapperWithPath($request);
        $response = new Response();

        try {
            $io = new Io(TestCommandNested::definition(), $requestMapper, $response);
            $io->getCommand()->performAction();
        } catch (\Exception $exception) {
            $response->error($exception->getMessage());
        }
    }

}
<?php

namespace YaoiTests\PHPUnit\Command;


use Yaoi\Command\Option;
use Yaoi\Command\Web\RequestMapper;
use Yaoi\Io\Request;
use Yaoi\Test\PHPUnit\TestCase;

class RequestMapperTest extends TestCase
{
    public function testRequestMapper()
    {
        $request = new Request();
        $request->server()->REQUEST_URI = '/the-first/get/';
        $request->setParam(Request::REQUEST, 'some_enum', 'one');

        $requestMapper = new RequestMapper($request);
        $options = array(
            Option::create()->setIsUnnamed()
        );
        $requestMapper->readOptions($options);
    }
}
<?php

namespace YaoiTests\PHPUnit\Cli;


use Yaoi\Cli\Command\RequestMapper;
use Yaoi\Test\PHPUnit\TestCase;
use YaoiTests\Helper\Command\TestCommandWithRequiredArgument;
use YaoiTests\Helper\Command\TestCommandWithRequiredOption;
use YaoiTests\Helper\TestRequestHelper;

class RequestReaderTest extends TestCase
{
    /**
     * @expectedException \Yaoi\Command\Exception
     * @expectedExceptionCode \Yaoi\Command\Exception::ARGUMENT_REQUIRED
     */
    public function testArgumentRequiredException()
    {
        $reader = new RequestMapper();
        $reader->read(
            TestRequestHelper::getCliRequest('--non-existent'),
            TestCommandWithRequiredArgument::optionsArray()
        );
    }

    /**
     * @expectedException \Yaoi\Command\Exception
     * @expectedExceptionCode \Yaoi\Command\Exception::OPTION_REQUIRED
     */
    public function testOptionRequiredException()
    {
        $reader = new RequestMapper();
        $reader->read(
            TestRequestHelper::getCliRequest('--optional'),
            TestCommandWithRequiredOption::optionsArray()
        );
    }

}
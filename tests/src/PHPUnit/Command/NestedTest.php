<?php

namespace YaoiTests\PHPUnit\Command;


use Yaoi\Cli\Response;
use Yaoi\Command\Exception;
use Yaoi\Command\Io;
use Yaoi\Command\Web\RequestMapper;
use Yaoi\Io\Request;
use Yaoi\Test\PHPUnit\TestCase;
use YaoiTests\Helper\Command\TestCommandNested;
use YaoiTests\Helper\Command\TestCommandOne;

/**
 * Class NestedTest
 * @package YaoiTests
 */
class NestedTest extends TestCase
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

    /**
     * @expectedException \Yaoi\Command\Exception
     * @expectedExceptionCode \Yaoi\Command\Exception::INVALID_VALUE
     * @expectedExceptionMessage Invalid value for `action`: one. Allowed values: the-first, test-command-with-required-option.
     */
    public function testNestedException1() {
        $request = new Request();
        $request->server()->REQUEST_URI = '/one/two/three?param1=3&param2=4';

        $requestMapper = new RequestMapper($request);
        $response = new Response();

        //try {
            $io = new Io(TestCommandNested::definition(), $requestMapper, $response);
            //$io->getCommand()->performAction();
        //} catch (\Exception $exception) {
          //  var_dump($exception->getTraceAsString());
            //$response->error($exception->getMessage());
        //}
    }


    /**
     * @expectedException \Yaoi\Command\Exception
     * @expectedExceptionCode \Yaoi\Command\Exception::INVALID_VALUE
     * @expectedExceptionMessage Invalid value for `action`: two. Allowed values: get, delete, create.
     */
    public function testNestedException2()
    {
        $request = new Request();
        $request->server()->REQUEST_URI = '/the-first/two/three?param1=3&param2=4';

        $requestMapper = new RequestMapper($request);
        $response = new Response();

        new Io(TestCommandNested::definition(), $requestMapper, $response);
    }


    /**
     * @throws Exception
     * @expectedException \Yaoi\Command\Exception
     * @expectedExceptionCode \Yaoi\Command\Exception::OPTION_REQUIRED
     * @expectedExceptionMessage Option `optionD` can not be empty
     */
    public function testNestedException3()
    {
        $request = new Request();
        $request->server()->REQUEST_URI = '/the-first/get/';
        $request->setParam(Request::REQUEST, 'some_enum', 'one');

        $requestMapper = new RequestMapper($request);
        $response = new Response();

        try {
            $io = new Io(TestCommandNested::definition(), $requestMapper, $response);
            $this->assertSame('one', $io->getCommand()->performAction());
        }
        catch (Exception $e) {
            //var_dump($e->getTraceAsString());
            throw $e;
        }
    }

    public function testMakeAnchor()
    {
        $request = new Request();
        $request->server()->REQUEST_URI = '/the-first/get/';
        $request->setParam(Request::REQUEST, 'some_enum', 'one')
            ->setParam(Request::REQUEST, 'option_d', 15);

        $requestMapper = new RequestMapper($request);
        $response = new Response();


        try {
            $io = new Io(TestCommandNested::definition(), $requestMapper, $response);
            $commandState = TestCommandOne::createState($io);
            $anchorExpression = $io->makeAnchor($commandState);
            $this->assertSame('/??/???option_d=??&some_enum=??', $anchorExpression->getStatement());
            $this->assertSame('/the-first/get?option_d=15&some_enum=one', (string)$anchorExpression);

            $commandState->optionD = 'abc';
            $this->assertSame('/the-first/get?option_d=abc&some_enum=one', (string)$io->makeAnchor($commandState));

            $commandState = TestCommandNested::createState($io);
            $commandState->action = 'someThing';
            $anchorExpression = $io->makeAnchor($commandState);
            var_dump($anchorExpression);
            $this->assertSame('/the-first/get?option_d=abc&some_enum=one', $anchorExpression->getStatement());



        }
        catch (Exception $e) {
            var_dump($e->getTraceAsString());
            throw $e;
        }

    }


    public function testZeroLevelAnchor()
    {
        $io = $this->getIo();
        try {
            $commandState = TestCommandNested::createState($io);
            $commandState->action = 'someThing';
            $anchorExpression = $io->makeAnchor($commandState);
            var_dump($anchorExpression);
            $this->assertSame('/the-first/get?option_d=abc&some_enum=one', (string)$anchorExpression);
        }
        catch (Exception $e) {
            var_dump($e->getTraceAsString());
            throw $e;
        }

    }




}
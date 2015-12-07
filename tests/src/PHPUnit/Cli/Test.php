<?php

namespace YaoiTests\PHPUnit\Cli;

use Yaoi\Cli\Runner;
use Yaoi\Request;
use Yaoi\String\Expression;
use Yaoi\Test\PHPUnit\TestCase;
use YaoiTests\Helper\Command\TestCommandOne;
use YaoiTests\Helper\Command\TestCommandWithNonTailingOptionalArgument;
use YaoiTests\Helper\Command\TestCommandWithOptionValue;
use YaoiTests\Helper\Command\TestCommandWithRequiredArgument;
use YaoiTests\Helper\Command\TestCommandWithSuccessMessage;
use YaoiTests\Helper\Command\TestCommandWithVariadicError;
use YaoiTests\Helper\Command\TestCommandWithVersion;
use Yaoi\Helpers;

class Test extends TestCase
{

    public function testHelp() {
        ob_start();
        Runner::create(new TestCommandOne())->showHelp();
        $result = ob_get_clean();
        //echo $result;
        //echo $this->varExportString($result);
        $expected = PHP_EOL
            . "\x1B" . '[36;1mthe-first' . "\x1B" . '[m' . PHP_EOL
            . 'This is a command one for doing nothing' . PHP_EOL
            . PHP_EOL
            . "\x1B" . '[36;1mUsage: ' . "\x1B" . '[m' . PHP_EOL
            . '    <action> [argumentA] [argumentB...] -d <optionD...> --some-enum <one|two|three>' . PHP_EOL
            . PHP_EOL
            . '   ' . "\x1B" . '[32;1maction   ' . "\x1B" . '[m   Main action                        ' . PHP_EOL
            . '               Allowed values: get, delete, create' . PHP_EOL
            . '   ' . "\x1B" . '[32;1margumentA' . "\x1B" . '[m   Bee description follows            ' . PHP_EOL
            . '   ' . "\x1B" . '[32;1margumentB' . "\x1B" . '[m   This is a variadic argument        ' . PHP_EOL
            . '   ' . PHP_EOL
            . "\x1B" . '[36;1mOptions: ' . "\x1B" . '[m' . PHP_EOL
            . '   ' . "\x1B" . '[32;1m--option-c                 ' . "\x1B" . '[m   Some option for the C                ' . PHP_EOL
            . '   ' . "\x1B" . '[32;1m-d <optionD...>            ' . "\x1B" . '[m   Short name option with required value' . PHP_EOL
            . '   ' . "\x1B" . '[32;1m--some-enum <one|two|three>' . "\x1B" . '[m   Enumerated option to set up something' . PHP_EOL
            . '                                 Allowed values: one, two, three      ' . PHP_EOL
            . '   ' . "\x1B" . '[32;1m--help                     ' . "\x1B" . '[m   Show usage information               ' . PHP_EOL
            . '   ' . "\x1B" . '[32;1m--version                  ' . "\x1B" . '[m   Show version                         ' . PHP_EOL
            . '   ' . PHP_EOL
        ;

        $this->assertSame($expected, $result);
    }

    public function testHelpVariadicError() {
        ob_start();
        Runner::create(new TestCommandWithVariadicError)->showHelp();
        $result = ob_get_clean();
        //echo $result;
        //echo $this->varExportString($result);
        $this->assertSame(PHP_EOL
            . "\x1B" . '[37;41m Command definition error: Non-tailing variadic argument [variadicArgument...] ' . "\x1B" . '[m' . PHP_EOL
            , $result);

    }

    public function testSuccessMessage() {
        ob_start();
        Runner::create(new TestCommandWithSuccessMessage)->init($this->getRequest(array()))->run();
        $result = ob_get_clean();
        //echo $result;
        //echo $this->varExportString($result);
        $this->assertSame("\x1B" . '[30;42m Congratulations! ' . "\x1B" . '[m' . PHP_EOL, $result);

    }

    public function testInit() {
        Runner::create(new TestCommandOne)->init($this->getRequest(array('get','123A','456B', '789B',
            '-d', 'd1', 'd2', 'd3',
            '--option-c',
            '--some-enum', 'two')));
    }


    /**
     * @expectedException \Yaoi\Cli\Exception
     * @expectedExceptionCode \Yaoi\Cli\Exception::OPTION_REQUIRED
     */
    public function testOptionRequired() {
        Runner::create(new TestCommandOne)->init($this->getRequest(array('get','123A','456B', '789B',
            //'-d', 'd1', 'd2', 'd3',
            '--option-c',
            '--some-enum', 'two')), true);
    }

    /**
     * @expectedException \Yaoi\Cli\Exception
     * @expectedExceptionCode \Yaoi\Cli\Exception::VALUE_REQUIRED
     */
    public function testArgumentRequired() {
        Runner::create(new TestCommandOne)->init($this->getRequest(array('get',
            '-d',
            '--option-c',
            '--some-enum', 'two')), true);
    }

    /**
     * @expectedException \Yaoi\Cli\Exception
     * @expectedExceptionCode \Yaoi\Cli\Exception::VALUE_REQUIRED
     */
    public function testArgumentRequired2() {
        Runner::create(new TestCommandWithOptionValue)->init($this->getRequest(array(
            '--value-option', //'value',
            '--bool-option',
        )), true
        )->run();
    }

    /**
     * @expectedException \Yaoi\Cli\Exception
     * @expectedExceptionCode \Yaoi\Cli\Exception::ARGUMENT_REQUIRED
     */
    public function testArgumentRequiredEmpty() {
        Runner::create(new TestCommandWithRequiredArgument)->init($this->getRequest(array()), true);
    }

    /**
     * @expectedException \Yaoi\Cli\Exception
     * @expectedExceptionCode \Yaoi\Cli\Exception::ARGUMENT_REQUIRED
     */
    public function testArgumentRequiredMissing() {
        Runner::create(new TestCommandWithRequiredArgument)->init($this->getRequest(array('arg1')), true);
    }

    /**
     * @expectedException \Yaoi\Cli\Exception
     * @expectedExceptionCode \Yaoi\Cli\Exception::ARGUMENT_REQUIRED
     */
    public function testArgumentRequiredOptionFound() {
        Runner::create(new TestCommandWithRequiredArgument)->init($this->getRequest(array('arg1', '--option')), true);
    }

    public function testVariadicArgument() {
        $command = new TestCommandWithRequiredArgument;
        Runner::create($command)->init($this->getRequest(array('arg1', 'arg2a', 'arg2b')));
        $this->assertSame(array('arg2a', 'arg2b'), $command->argumentTwo);
    }

    public function testUnifiedOption() {
        ob_start();
        Runner::create(new TestCommandWithOptionValue)->showHelp();
        $result = ob_get_clean();
        //echo $this->varExportString($result);
        $expected = PHP_EOL
            . "\x1B" . '[36;1mUsage: ' . "\x1B" . '[m' . PHP_EOL
            . '   ' . PHP_EOL
            . "\x1B" . '[36;1mOptions: ' . "\x1B" . '[m' . PHP_EOL
            . '   ' . "\x1B" . '[32;1m--value-option <valueOption>' . "\x1B" . '[m                         ' . PHP_EOL
            . '   ' . "\x1B" . '[32;1m--bool-option               ' . "\x1B" . '[m                         ' . PHP_EOL
            . '   ' . "\x1B" . '[32;1m--unified-option            ' . "\x1B" . '[m                         ' . PHP_EOL
            . '   ' . "\x1B" . '[32;1m--help                      ' . "\x1B" . '[m   Show usage information' . PHP_EOL
            . '   ' . "\x1B" . '[32;1m--version                   ' . "\x1B" . '[m   Show version          ' . PHP_EOL
            . '   ' . PHP_EOL;
        $this->assertSame($expected, $result);

    }

    /**
     * @expectedException \Yaoi\Cli\Exception
     * @expectedExceptionCode \Yaoi\Cli\Exception::UNKNOWN_OPTION
     */
    public function testUnknownOption() {
        Runner::create(new TestCommandWithOptionValue)->init($this->getRequest(array(
            '--value-option', 'value',
            '--bool-option',
            '--unknown-option'
        )), true
        )->run();
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
                    'argv' => array_merge(array('script.php'), $argv),
                    'argc' => count($argv) + 1,
                )),
            'isCli' => true,
        ));
        return $request;
    }

    /**
     * @expectedException \Yaoi\Cli\Exception
     * @expectedExceptionCode \Yaoi\Cli\Exception::NON_TAILING_OPTIONAL_ARGUMENT
     */
    public function testNonTailingOptionalArgument() {
        Runner::create(new TestCommandWithNonTailingOptionalArgument)->init(
            $this->getRequest(array('get','123A','456B', '789B',
            //'-d', 'd1', 'd2', 'd3',
            '--option-c', 'THE-C-VALUE',
            '--some-enum', 'two')), true
        );
    }


    public function testHelpRun() {
        ob_start();
        Runner::create(new TestCommandOne)->init($this->getRequest(array('--help')))->run();
        $result = ob_get_clean();
        //echo $this->varExportString($result);
        $expected = PHP_EOL
            . "\x1B" . '[36;1mthe-first' . "\x1B" . '[m' . PHP_EOL
            . 'This is a command one for doing nothing' . PHP_EOL
            . PHP_EOL
            . "\x1B" . '[36;1mUsage: ' . "\x1B" . '[m' . PHP_EOL
            . '    <action> [argumentA] [argumentB...] -d <optionD...> --some-enum <one|two|three>' . PHP_EOL
            . PHP_EOL
            . '   ' . "\x1B" . '[32;1maction   ' . "\x1B" . '[m   Main action                        ' . PHP_EOL
            . '               Allowed values: get, delete, create' . PHP_EOL
            . '   ' . "\x1B" . '[32;1margumentA' . "\x1B" . '[m   Bee description follows            ' . PHP_EOL
            . '   ' . "\x1B" . '[32;1margumentB' . "\x1B" . '[m   This is a variadic argument        ' . PHP_EOL
            . '   ' . PHP_EOL
            . "\x1B" . '[36;1mOptions: ' . "\x1B" . '[m' . PHP_EOL
            . '   ' . "\x1B" . '[32;1m--option-c                 ' . "\x1B" . '[m   Some option for the C                ' . PHP_EOL
            . '   ' . "\x1B" . '[32;1m-d <optionD...>            ' . "\x1B" . '[m   Short name option with required value' . PHP_EOL
            . '   ' . "\x1B" . '[32;1m--some-enum <one|two|three>' . "\x1B" . '[m   Enumerated option to set up something' . PHP_EOL
            . '                                 Allowed values: one, two, three      ' . PHP_EOL
            . '   ' . "\x1B" . '[32;1m--help                     ' . "\x1B" . '[m   Show usage information               ' . PHP_EOL
            . '   ' . "\x1B" . '[32;1m--version                  ' . "\x1B" . '[m   Show version                         ' . PHP_EOL
            . '   ' . PHP_EOL;
        $this->assertSame($expected, $result);
    }

    public function testVersionRun() {
        ob_start();
        Runner::create(new TestCommandWithVersion)->init($this->getRequest(array('--version')))->run();
        $result = ob_get_clean();
        //echo $result;
        //echo $this->varExportString($result);
        $expected = PHP_EOL
            . "\x1B" . '[36;1mv1.0 ' . "\x1B" . '[m' . "\x1B" . '[36;1mcli-cli-cli' . "\x1B" . '[m' . PHP_EOL
            . 'Test command with version' . PHP_EOL
            . PHP_EOL;

        $this->assertSame($expected, $result);
    }

    public function testError() {
        ob_start();
        $runner = Runner::create(new TestCommandWithVersion);
        $runner->error(new Expression("?, ?!", 'hello', 'world'));
        $runner->success(new Expression("?, ?!", 'hello', 'world'));
        $runner->error('hello, world!');
        $runner->success('hello, world!');
        $result = ob_get_clean();
        //echo $this->varExportString($result);
        $expected = "\x1B" . '[37;41m hello, world! ' . "\x1B" . '[m' . PHP_EOL
            . "\x1B" . '[30;42m hello, world! ' . "\x1B" . '[m' . PHP_EOL
            . "\x1B" . '[37;41m hello, world! ' . "\x1B" . '[m' . PHP_EOL
            . "\x1B" . '[30;42m hello, world! ' . "\x1B" . '[m' . PHP_EOL;

        $this->assertSame($expected, $result);
    }
}
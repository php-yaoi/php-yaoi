<?php

namespace Yaoi\Test\PHPUnit;
use PHPUnit_Framework_TestCase;

class TestCase extends PHPUnit_Framework_TestCase
{
    public static $settings = array();
    private static $totalRuntime = 0;
    private $verbose;
    private $debug;

    public function runTest()
    {

        $this->verbose = in_array('--verbose', $_SERVER['argv'], true) || in_array('-v', $_SERVER['argv'], true);
        $this->debug = in_array('--debug', $_SERVER['argv'], true);

        $s = microtime(1);
        parent::runTest();
        $s = microtime(1) - $s;
        self::$totalRuntime += $s;
        if ($this->debug) {
            echo "\t", round(1000 * $s), " ms.,\t tot. ", round(1000 * self::$totalRuntime), " ms.\n";
        }
    }


    public function assertStringEqualsCRLF($expected, $actual, $message = '')
    {
        $expected = str_replace("\r\n", "\n", $expected);
        $actual = str_replace("\r\n", "\n", $actual);
        $this->assertSame($expected, $actual, $message);
    }


    public function assertStringEqualsSpaceless($expected, $actual, $message = '')
    {
        $expected = preg_replace("/\s+/", " ", $expected);
        $actual = preg_replace("/\s+/", " ", $actual);
        $this->assertSame($expected, $actual, $message);
    }


    public function assertArrayBitwiseAnd($expected, $actual, $message = '')
    {
        foreach ($expected as $key => $value) {
            $this->assertNotEmpty($value & $actual[$key], $message);
        }
    }

    public function varExportString($string) {
        static $specialChars = array(
            "\r" => '\r',
            "\n" => '\n',
            "\t" => '\t',
        );
        $result = '';
        $started = false;
        //*
        for ($i = 0; $i < strlen($string); ++$i) {

            $char = $string[$i];
            $ord = ord($char);
            if ($ord < 32) {
                if (isset($specialChars[$char])) {
                    $char = $specialChars[$char];
                }
                else {
                    $char = strtoupper(dechex($ord));
                    if (strlen($char) < 2) {
                        $char = '0' . $char;
                    }
                    $char = '\x' . $char;
                }
                if ($started === '\'') {
                    $result .= '\' . "';
                }
                elseif (!$started) {
                    $result .= $result ? '. "' : '"';
                }
                $started = '"';
            }
            else {
                if ($started === '"') {
                    $result .= '" . \'';
                }
                elseif (!$started) {
                    $result .= $result ? '. \'' : '\'';
                }
                $started = '\'';
            }
            $result .= $char;

            if ('\n' === $char) {
                $result .= '"' . PHP_EOL;
                $started = '';
            }

        }
        $result .= $started;
        return $result;
    }
} 
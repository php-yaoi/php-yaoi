<?php

class Test_PHPUnit_Case  extends PHPUnit_Framework_TestCase {
    public static $settings = array();
    private static $totalRuntime = 0;

    public function runTest() {
        echo 'testing ' , str_pad(get_called_class() . '->' . $this->getName() . ',', 50, ' ');
        $s = microtime(1);
        parent::runTest();
        $s = microtime(1) - $s;
        self::$totalRuntime += $s;
        echo "\t", round(1000 * $s), " ms.,\t tot. ", round(1000 * self::$totalRuntime), " ms.\n";
    }



    public function assertStringEqualsCRLF($expected, $actual, $message = '') {
        $expected = str_replace("\r\n", "\n", $expected);
        $actual = str_replace("\r\n", "\n", $actual);
        $this->assertSame($expected, $actual, $message);
    }
} 
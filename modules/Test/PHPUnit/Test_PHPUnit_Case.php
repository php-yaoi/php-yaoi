<?php

class Test_PHPUnit_Case  extends PHPUnit_Framework_TestCase {
    public static $settings = array();

    public function runTest() {
        echo 'testing ' , str_pad(get_called_class() . '->' . $this->getName() . ',', 50, ' ');
        $s = microtime(1);
        parent::runTest();
        echo "\t", round(1000 * (microtime(1) - $s)), " ms.\n";
    }
} 
<?php

class Test_PHPUnit_Case  extends PHPUnit_Framework_TestCase {
    public static $settings = array();

    public function runTest() {
        echo 'testing ' , get_called_class(), '->', $this->getName() , "\n";
        parent::runTest();
    }
} 
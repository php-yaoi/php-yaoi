<?php

class DatabaseEntityMysqliTest extends \YaoiTests\DatabaseEntity\TestCase
{
    public function setUp() {
        try {
            $this->database = \Yaoi\Database::getInstance('test_mysqli');
        }
        catch (\Yaoi\Service\Exception $exception) {
            $this->markTestSkipped($exception->getMessage());
        }

    }

}
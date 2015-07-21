<?php

namespace YaoiTests\Http\Client;


use Yaoi\Test\PHPUnit\TestCase;

class MirrorTestCase extends TestCase
{
    public function setUp() {
        if (empty(TestCase::$settings['envHttpMirrorServer'])) {
            $this->markTestSkipped('HttpMirror tests disabled');
            return;
        }

    }

}
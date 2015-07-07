<?php

namespace PHPUnit\Service;
use Yaoi\Date\TimeMachine;
use Yaoi\Service\Settings;
use Yaoi\Test\PHPUnit\TestCase;
use YaoiTests\Service\BasicExposed;
use YaoiTests\Service\NoSettings;

class SettingsTest extends TestCase
{

    /**
     *
     */
    public function testNullSettings() {
        $settings = new Settings();
        $this->assertSame($settings, NoSettings::create($settings)->getSettings());
        $this->assertTrue(NoSettings::create(null)->getSettings() instanceof $settings);
    }


    /**
     * DSN in URI scheme
     *
     * @throws \Yaoi\Service\Exception
     */
    public function testDsnSettings() {
        $dsn = 'scheme://user:password@host:1234/path?flag1=one&flag2=two';
        $expected = array (
            'driverClassName' => NULL,
            'identifier' => NULL,
            'scheme' => 'scheme',
            'username' => 'user',
            'password' => 'password',
            'hostname' => 'host',
            'port' => 1234,
            'path' => 'path',
            'flag1' => 'one',
            'flag2' => 'two',
        );
        $this->assertArraySubset($expected, (array)BasicExposed::create($dsn)->getSettings());

        $expected = new Settings();
        $this->assertSame($expected, BasicExposed::create($expected)->getSettings());
    }

}
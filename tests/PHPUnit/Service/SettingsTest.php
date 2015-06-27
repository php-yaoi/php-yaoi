<?php

namespace PHPUnit\Service;
use Yaoi\Date\TimeMachine;
use Yaoi\Service\Settings;
use Yaoi\Test\PHPUnit\TestCase;
use YaoiTests\Service\BasicExposed;
use YaoiTests\Service\NoSettings;

class SettingsTest extends TestCase
{

    public function testNullSettings() {
        $settings = new Settings();
        $this->assertSame($settings, NoSettings::settings($settings));
        $this->assertTrue(NoSettings::settings(null) instanceof $settings);
    }


    /**
     * DSN in URI scheme
     *
     * @throws \Yaoi\Service\Exception
     */
    public function testDsnSettings() {
        $dsn = 'scheme://user:password@host:1234/path?flag1=one&flag2=two';
        $settings = \YaoiTests\Service\BasicExposed::settings($dsn);
        $this->assertTrue(BasicExposed::createSettings() instanceof $settings);
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
        $this->assertArraySubset($expected, (array)$settings);

        $expected = new \Yaoi\Service\Settings();

        $settings = BasicExposed::settings(function()use($expected){
            return $expected;
        });

        $this->assertSame($expected, $settings);
    }

}
<?php
/**
 * Created by PhpStorm.
 * User: vpoturaev
 * Date: 6/27/15
 * Time: 10:46
 */

namespace PHPUnit\Service;

use Yaoi\Service\Settings;
use Yaoi\Test\PHPUnit\TestCase;
use YaoiTests\Service\BasicExposed;

class RegisterTest extends TestCase
{
    /**
     * To register a service instance you can use DSN url
     * @throws \Yaoi\Service\Exception
     * @see \Yaoi\Service::register
     */
    public function testRegisterDsn() {
        BasicExposed::register('test-id1', 'scheme://user:password@host-id1:1234/path?flag1=one&flag2=two');
        $test1 = BasicExposed::getInstance('test-id1');
        $this->assertSame('host-id1', $test1->getSettings()->hostname);
    }

    /**
     * Or you can use Closure that returns valid Settings class for your service
     * @throws \Yaoi\Service\Exception
     * @see \Yaoi\Service::register
     */
    public function testRegisterClosure() {
        BasicExposed::register('test-id2', function(){
            $settings = BasicExposed::createSettings();
            $settings->hostname = 'host-id2';
            return $settings;
        });
        $test2 = BasicExposed::getInstance('test-id2');
        $this->assertSame('host-id2', $test2->getSettings()->hostname);
    }

    /**
     * @expectedExceptionCode \Yaoi\Service\Exception::SETTINGS_REQUIRED
     * @expectedException \Yaoi\Service\Exception
     * @throws \Yaoi\Service\Exception
     */
    public function testRegisterBadClosure() {
        BasicExposed::register('test-id2-bad', function(){
            $className = new \stdClass();
            $settings = new $className;
            $settings->hostname = 'host-id2-bad';
            return $settings;
        });
        $test2 = BasicExposed::getInstance('test-id2-bad');
        $this->assertSame('host-id2-bad', $test2->getSettings()->hostname);
    }


    /**
     * $settings value can also be reference to another identifier
     * @throws \Yaoi\Service\Exception
     * @see \Yaoi\Service::register
     */
    public function testRegisterIdentifier() {
        BasicExposed::register('test-id3', 'test://test');
        BasicExposed::register('test-id4', 'test-id3');

        $this->assertSame(BasicExposed::getInstance('test-id3'), BasicExposed::getInstance('test-id4'));
    }

    /**
     * $settings value can be instance of settings class
     * @throws \Yaoi\Service\Exception
     * @see \Yaoi\Service::register
     */
    public function testRegisterSettings() {
        $settings = new Settings();
        $settings->hostname = 'host-id6';

        BasicExposed::register('test-id6', $settings);
        $this->assertSame($settings, BasicExposed::getInstance('test-id6')->getSettings());
    }

    /**
     * @expectedExceptionCode \Yaoi\Service\Exception::SETTINGS_REQUIRED
     * @expectedException \Yaoi\Service\Exception
     * @throws \Yaoi\Service\Exception
     */
    public function testRegisterInvalidSettings() {
        BasicExposed::register('test-id7-bad', new \stdClass());
        $test2 = BasicExposed::getInstance('test-id7-bad');
        $this->assertSame('host-id7-bad', $test2->getSettings()->hostname);
    }
}
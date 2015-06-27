<?php
namespace PHPUnit\Service;

use Yaoi\Service;
use YaoiTests\Service\Another;
use YaoiTests\Service\BasicExposed;
use YaoiTests\Service\DefaultPrimary;
use YaoiTests\Service\NoSettings;

class GetInstanceTest extends \Yaoi\Test\PHPUnit\TestCase
{

    /**
     * If you specify $identifier of wrong type you will get `Service\Exception`
     * @see Service::getInstance
     *
     * @expectedExceptionCode \Yaoi\Service\Exception::INVALID_ARGUMENT
     * @expectedException \Yaoi\Service\Exception
     */
    public function testSettingsInvalidArgument() {
        BasicExposed::getInstance(new \stdClass());
    }


    /**
     * Primary instance is returned by default
     *
     * @throws \Yaoi\Service\Exception
     * @see \Yaoi\Service::getInstance
     */
    public function testPrimary() {
        BasicExposed::register(Service::PRIMARY, 'test');
        $this->assertSame(BasicExposed::getInstance(), BasicExposed::getInstance(Service::PRIMARY));
    }

    /**
     * If fallback is set, it will be returned for unknown instances
     *
     * @throws \Yaoi\Service\Exception
     * @see \Yaoi\Service::getInstance
     */
    public function testFallback() {
        Another::register(Service::FALLBACK, 'test');
        Another::register('known', 'test2');

        $this->assertSame(Another::getInstance(), Another::getInstance('unknown'));
        $this->assertNotSame(Another::getInstance('unknown'), Another::getInstance('known'));
    }

    /**
     * If no fallback is set exception will be thrown
     *
     * @expectedExceptionCode \Yaoi\Service\Exception::NO_FALLBACK
     * @expectedException \Yaoi\Service\Exception
     * @throws \Yaoi\Service\Exception
     * @see \Yaoi\Service::getInstance
     */
    public function testMissingFallback() {
        BasicExposed::getInstance('unknown');
    }


    /**
     * Any object of class Service or its descendant can be passed as argument and returned unchanged
     *
     * @throws \Yaoi\Service\Exception
     * @see \Yaoi\Service::getInstance
     */
    public function testServicePassThrough() {
        $test1 = new Another();

        $this->assertSame($test1, BasicExposed::getInstance($test1));
        $this->assertSame($test1, Another::getInstance($test1));
    }


    public function testSettingsGetInstance() {
        $settings = BasicExposed::createSettings();

        $this->assertSame($settings, BasicExposed::getInstance($settings)->getSettings());
    }


    public function testNullSettings() {
        NoSettings::register('test1', null);
        NoSettings::getInstance('test1');
    }


    public function testDefaultFallback() {
        DefaultPrimary::getInstance();
    }


}
<?php

class ServiceTest extends \Yaoi\Test\PHPUnit\TestCase
{
    /**
     * To register a service instance you can use DSN url
     * @throws \Yaoi\Service\Exception
     * @see \Yaoi\Service::register
     */
    public function testRegisterDsn() {
        TestService::register('test-id1', 'scheme://user:password@host-id1:1234/path?flag1=one&flag2=two');
        $test1 = TestService::getInstance('test-id1');
        $this->assertSame('host-id1', $test1->getSettings()->hostname);
    }

    /**
     * Or you can use Closure that returns valid Settings class for your service
     * @throws \Yaoi\Service\Exception
     * @see \Yaoi\Service::register
     */
    public function testRegisterClosure() {
        TestService::register('test-id2', function(){
            $className = TestService::getSettingsClassName();
            $settings = new $className;
            $settings->hostname = 'host-id2';
            return $settings;
        });
        $test2 = TestService::getInstance('test-id2');
        $this->assertSame('host-id2', $test2->getSettings()->hostname);
    }

    /**
     * @expectedExceptionCode \Yaoi\Service\Exception::SETTINGS_REQUIRED
     * @expectedException \Yaoi\Service\Exception
     * @throws \Yaoi\Service\Exception
     */
    public function testRegisterBadClosure() {
        TestService::register('test-id2-bad', function(){
            $className = new stdClass();
            $settings = new $className;
            $settings->hostname = 'host-id2-bad';
            return $settings;
        });
        $test2 = TestService::getInstance('test-id2-bad');
        $this->assertSame('host-id2-bad', $test2->getSettings()->hostname);
    }


    /**
     * $settings value can also be reference to another identifier
     * @throws \Yaoi\Service\Exception
     * @see \Yaoi\Service::register
     */
    public function testRegisterIdentifier() {
        TestService::register('test-id3', 'test://test');
        TestService::register('test-id4', 'test-id3');

        $this->assertSame(TestService::getInstance('test-id3'), TestService::getInstance('test-id4'));
    }

    /**
     * $settings value can be instance of settings class
     * @throws \Yaoi\Service\Exception
     * @see \Yaoi\Service::register
     */
    public function testRegisterSettings() {
        $settings = new \Yaoi\Service\Settings();
        $settings->hostname = 'host-id6';

        TestService::register('test-id6', $settings);
        $this->assertSame($settings, TestService::getInstance('test-id6')->getSettings());
    }

    /**
     * @expectedExceptionCode \Yaoi\Service\Exception::SETTINGS_REQUIRED
     * @expectedException \Yaoi\Service\Exception
     * @throws \Yaoi\Service\Exception
     */
    public function testRegisterInvalidSettings() {
        TestService::register('test-id7-bad', new stdClass());
        $test2 = TestService::getInstance('test-id7-bad');
        $this->assertSame('host-id7-bad', $test2->getSettings()->hostname);
    }




    /**
     * @expectedExceptionCode \Yaoi\Service\Exception::INVALID_ARGUMENT
     * @expectedException \Yaoi\Service\Exception
     */
    public function testSettingsInvalidArgument() {
        TestService::getInstance(new stdClass());
    }


    /**
     * DSN in URI scheme
     *
     * @throws \Yaoi\Service\Exception
     */
    public function testDsnSettings() {
        $dsn = 'scheme://user:password@host:1234/path?flag1=one&flag2=two';
        $settings = TestService::settings($dsn);
        $this->assertInstanceOf(TestService::getSettingsClassName(), $settings);
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

        $settings = TestService::settings(function()use($expected){
            return $expected;
        });

        $this->assertSame($expected, $settings);
    }

    /**
     * Primary instance is returned by default
     *
     * @throws \Yaoi\Service\Exception
     * @see \Yaoi\Service::getInstance
     */
    public function testPrimary() {
        TestService::register(\Yaoi\Service::PRIMARY, 'primary');
        $this->assertSame(TestService::getInstance(), TestService::getInstance(\Yaoi\Service::PRIMARY));
    }

    /**
     * If fallback is set, it will be returned for unknown instances
     *
     * @throws \Yaoi\Service\Exception
     * @see \Yaoi\Service::getInstance
     */
    public function testFallback() {
        TestServiceTwo::register(Yaoi\Service::FALLBACK, 'test');
        TestServiceTwo::register('known', 'test2');

        $this->assertSame(TestServiceTwo::getInstance(), TestServiceTwo::getInstance('unknown'));
        $this->assertNotSame(TestServiceTwo::getInstance('unknown'), TestServiceTwo::getInstance('known'));
    }

    /**
     * If no fallback is set exception will be thrown
     *
     * @expectedExceptionCode \Yaoi\Service\Exception::NO_FALLBACK
     * @expectedException \Yaoi\Service\Exception
     * @throws \Yaoi\Service\Exception
     * @see \Yaoi\Service::getInstance
     */
    /*
    public function testMissingFallback() {
        TestService::getInstance('unknown');
    }
    */


    /**
     * Any object of class Service or its descendant can be passed as argument and returned unchanged
     *
     * @throws \Yaoi\Service\Exception
     * @see \Yaoi\Service::getInstance
     */
    public function testServicePassThrough() {
        $test1 = new TestServiceTwo();

        $this->assertSame($test1, TestService::getInstance($test1));
        $this->assertSame($test1, TestServiceTwo::getInstance($test1));
    }


    public function testSettingsGetInstance() {
        $settings = TestService::createSettings();

        $this->assertSame($settings, TestService::getInstance($settings)->getSettings());
    }



}

class TestService extends Yaoi\Service {
    public static function getSettingsClassName()
    {
        return \Yaoi\Service\Settings::className();
    }
}

class TestServiceTwo extends TestService {}
<?php

namespace CustomVendor;

use Yaoi\Storage;

class ServiceDriverTest extends \Yaoi\Test\PHPUnit\TestCase
{
    /**
     * You can use drivers from custom namespace
     *
     * @see Service::getDriver
     */
    public function testCustomDriverDsn() {
        $dsn = 'custom-vendor.driver-class://user:pass@host:1234/path1/path2?param1=1&param2=2';
        $storage = new Storage($dsn);

        $this->assertTrue($storage->getDriver() instanceof \CustomVendor\Yaoi\Storage\Driver\DriverClass);
    }

    /**
     * You can specify any class for driver explicitly with `driverClassName` property of your settings
     *
     * @see Settings::driverClassName
     * @example
     */
    public function testCustomDriverClassName() {
        $settings = Storage::createSettings();
        $settings->driverClassName = \CustomVendor\Yaoi\Storage\Driver\DriverClass::className();

        $storage = new Storage($settings);
        $this->assertTrue($storage->getDriver() instanceof \CustomVendor\Yaoi\Storage\Driver\DriverClass);
    }

    /**
     * For setting standard drivers you can use DSN schema field
     *
     * @see Service::getDriver
     */
    public function testDsn() {
        $dsn = 'void://user:pass@host:1234/path1/path2?param1=1&param2=2';
        $storage = new Storage($dsn);

        $this->assertTrue($storage->getDriver() instanceof \Yaoi\Storage\Driver\Void);
    }

    /**
     * If you are trying to set non existent driver you will get an Exception
     *
     * @expectedExceptionCode \Yaoi\Service\Exception::NO_DRIVER
     * @expectedException \Yaoi\Service\Exception
     */
    public function testNonExistent() {
        $storage = new Storage('non-existent');
        $storage->getDriver();
    }


    public function testForceDriver() {

    }
}


require_once __DIR__ . '/TestServiceThree_TestDriver.php';

class TestServiceThree extends \Yaoi\Service {
    /**
     * @return \Yaoi\Service\Settings
     */
    public static function getSettingsClassName()
    {
        return \Yaoi\Service\Settings::className();
    }

}

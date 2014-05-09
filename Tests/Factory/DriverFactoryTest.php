<?php

namespace Tedivm\StashBundle\Tests\Factory;

use Tedivm\StashBundle\Factory\DriverFactory;
use Stash\Drivers;

class DriverFactoryTest extends \PHPUnit_Framework_TestCase
{
    protected $drivers = array();

    protected $defaultSettings = array(
        'FileSystem' => array(
            'dirSplit'          => 2,
            'filePermissions'   => 0660,
            'dirPermissions'    => 0770,
            'memKeyLimit'       => 200
        ),
        'SQLite' => array(
            'filePermissions'   => 0660,
            'dirPermissions'    => 0770,
            'busyTimeout'       => 500,
            'nesting'           => 0,
            'subdriver'        => 'PDO',
            'version'           => null,
        ),
        'Apc' => array(
            'ttl'               => 300,
            'namespace'         => null,
        ),
        'Memcache' => array(
            'servers' => array(
                array(
                    'server' => '127.0.0.1',
                    'port' => '11211'
                ),
                array(
                    'server' => '127.0.0.1',
                    'port' => '11212',
                    'weight' => '30'
                ),
                array(
                    'server' => '127.0.0.1',
                    'port' => '11211',
                    'weight' => '30'
                ),
            )
        )
    );

    public function setUp()
    {
        $this->drivers = Drivers::getDrivers();
    }

    /**
     * @dataProvider driverProvider
     */
    public function testManufactureDrivers($types, $options)
    {
        $driver = DriverFactory::createDriver($types, $options);

        if (count($types) > 1) {
            $driverClass = $this->drivers['Composite'];
            $h = $this->getObjectAttribute($driver, 'drivers');
            $drivers = array_combine($types, $h);
        } else {
            $driverClass = $this->drivers[$types[0]];
            $drivers = array($types[0] => $driver);
        }

        $this->assertInstanceOf('Stash\Interfaces\DriverInterface', $driver);
        $this->assertInstanceOf($driverClass, $driver);

        foreach ($drivers as $subtype => $subDriver) {
            $subDriverClass = $this->drivers[$subtype];
            $this->assertInstanceOf($subDriverClass, $subDriver);
        }

    }

    public function testMemcacheSetup()
    {
        if(!isset($this->drivers['Memcache'])) {
            $this->markTestSkipped('Memcache extension required for this test.');
        }

        $driver = DriverFactory::createDriver(array('Memcache'), $this->defaultSettings['Memcache']);
        $this->assertInstanceOf('Stash\Interfaces\DriverInterface', $driver);
    }


    /**
     *
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Driver does not exist.
     */
    public function testFakeDriverException()
    {
        DriverFactory::createDriver(array('FakeDriver'), array());
    }

    /**
     *
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Driver currently unavailable.
     */
    public function testUnavailableDriverException()
    {
        Drivers::registerDriver('FakeDriver', 'Stash\Test\Stubs\DriverUnavailableStub');
        DriverFactory::createDriver(array('FakeDriver'), array());
    }


    public function driverProvider()
    {
        return array(
            array(
                'types'     => array('FileSystem'),
                'options'   => array(),
            ),
            array(
                'types'     => array('FileSystem'),
                'options'   => array('FileSystem' => array('dirSplit' => 3, 'memKeyLimit' => 21)),
            ),
            array(
                'types'     => array('SQLite'),
                'options'   => array(),
            ),
            array(
                'types'     => array('SQLite'),
                'options'   => array('nesting' => 2, 'extension' => 'sqlite'),
            ),
            array(
                'types'     => array('Ephemeral', 'FileSystem'),
                'options'   => array('FileSystem' => array('dirSplit' => 2)),
            ),
            array(
                'types'     => array('Ephemeral', 'FileSystem', 'SQLite'),
                'options'   => array('FileSystem' => array('dirSplit' => 3), 'SQLite' => array('dirSplit' => 5)),
            ),
        );
    }
}

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
            $driverclass = $this->drivers['Composite'];
            $h = $this->getObjectAttribute($driver, 'drivers');
            $drivers = array_combine($types, $h);
        } else {
            $driverclass = $this->drivers[$types[0]];
            $drivers = array($types[0] => $driver);
        }

        $this->assertInstanceOf($driverclass, $driver);

        foreach ($drivers as $subtype => $subdriver) {
            $subdriverclass = $this->drivers[$subtype];
            $this->assertInstanceOf($subdriverclass, $subdriver);
        }

        foreach ($types as $type) {
            $defaults = isset($this->defaultSettings[$type]) ? $this->defaultSettings[$type] : array();
            $options = array_merge($defaults, $options);

/*            foreach ($options as $optname => $optvalue) {
                $this->assertAttributeEquals($optvalue, $optname, $drivers[$type]);
            }
*/
        }
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

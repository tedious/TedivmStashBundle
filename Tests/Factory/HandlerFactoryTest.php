<?php

namespace Tedivm\StashBundle\Tests\Factory;

use Tedivm\StashBundle\Factory\HandlerFactory;
use Stash\Utilities;
use Stash\Drivers;

class HandlerFactoryExtensionTest extends \PHPUnit_Framework_TestCase
{
    protected $handlers = array();

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
            'subhandler'        => 'PDO',
            'version'           => null,
        ),
        'Apc' => array(
            'ttl'               => 300,
            'namespace'         => null,
        ),
    );

    public function setUp()
    {
        $this->handlers = Drivers::getDrivers();
    }

    /**
     * @dataProvider handlerProvider
     */
    public function testManufactureHandlers($types, $options)
    {
        $handler = HandlerFactory::createHandler($types, $options);

        if(count($types) > 1) {
            $handlerclass = $this->handlers['MultiHandler'];
            $h = \PHPUnit_Util_Class::getObjectAttribute($handler, 'handlers');
            $handlers = array_combine($types, $h);
        } else {
            $handlerclass = $this->handlers[$types[0]];
            $handlers = array($types[0] => $handler);
        }

        $this->assertInstanceOf($handlerclass, $handler);

        foreach($handlers as $subtype => $subhandler) {
            $subhandlerclass = $this->handlers[$subtype];
            $this->assertInstanceOf($subhandlerclass, $subhandler);
        }

        foreach($types as $type) {
            $defaults = isset($this->defaultSettings[$type]) ? $this->defaultSettings[$type] : array();
            $options = array_merge($defaults, $options);

/*            foreach($options as $optname => $optvalue) {
                $this->assertAttributeEquals($optvalue, $optname, $handlers[$type]);
            }
*/
        }
    }

    public function handlerProvider()
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

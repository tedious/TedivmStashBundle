<?php

/*
 * This file is part of the StashBundle package.
 *
 * (c) Josh Hall-Bachner <jhallbachner@gmail.com>
 * (c) Robert Hafner <tedivm@tedivm.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tedivm\StashBundle\Tests\DependencyInjection;

use Tedivm\StashBundle\DependencyInjection\TedivmStashExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class TedivmStashExtensionTest
 * @package Tedivm\StashBundle\Tests\DependencyInjection
 * @author Josh Hall-Bachner <jhallbachner@gmail.com>
 * @author Robert Hafner <tedivm@tedivm.com>
 */
class TedivmStashExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider configProvider
     */
    public function testLoadDriverConfiguration($config)
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'test');
        $extension = new TedivmStashExtension();

        $extension->load(array($config), $container);

        $this->assertEquals($config['default_cache'], $container->getParameter('stash.default_cache'));
        $this->assertEquals(count($config['caches']), count($container->getParameter('stash.caches')));

        $options = $container->getParameter('stash.caches.options');
        foreach ($config['caches'] as $name => $cache) {
            $cacheoptions = $options[$name];
            $this->assertArrayHasKey($name, $container->getParameter('stash.caches'));

            foreach (array('inMemory') as $item) {
                $value = isset($cache[$item]) ? $cache[$item] : true;
                $this->assertEquals($cacheoptions[$item], $value);
            }

            foreach (array('registerSessionHandler', 'registerDoctrineAdapter') as $item) {
                $value = isset($cache[$item]) ? $cache[$item] : false;
                $this->assertEquals($cacheoptions[$item], $value);
            }

            if (isset($cache['drivers'])) {
                foreach ($cache['drivers'] as $driver) {
                    $driveroptions = $cache[$driver];
                    foreach ($driveroptions as $driveroptname => $driveroptvalue) {
                        $this->assertEquals($driveroptvalue, $cacheoptions[$driver][$driveroptname]);
                    }
                }
            }
        }
    }

    public function testGetAlias()
    {
        $extension = new TedivmStashExtension();
        $this->assertEquals('stash', $extension->getAlias(), 'getAlias returns "stash"');
    }

    public function configProvider()
    {
        return array(
            array(
                'config' => array(
                    'default_cache' => 'first',
                    'caches' => array(
                        'first' => array(
                            'drivers' => array('FileSystem'),
                            'FileSystem' => array(
                                'dirSplit'          => 2,
                                'path'              => '%kernel.cache_dir%/stash',
                                'filePermissions'   => 0660,
                                'dirPermissions'    => 0770,
                                'memKeyLimit'       => 400
                            ),
                        )
                    ),
                ),
            ),

            array(
                'config' => array(
                    'default_cache' => 'first',
                    'caches' => array(
                        'first' => array(),
                        )
                    ),
                ),

            array(
                'config' => array(
                    'default_cache' => 'default',
                    'caches' => array(
                        'default' => array(
                            'drivers' => array('SQLite'),
                            'registerDoctrineAdapter' => true,
                            'registerSessionHandler' => false,
                            'inMemory' => true,
                            'SQLite' => array(
                                'filePermissions'   => 0550,
                                'dirPermissions'    => 0444,
                                'path'              => '%kernel.cache_dir%/tedivm/stash',
                            ),
                        ),
                        'nondefault' => array(
                            'drivers' => array('FileSystem', 'SQLite'),
                            'registerDoctrineAdapter' => true,
                            'registerSessionHandler' => true,
                            'inMemory' => true,
                            'FileSystem' => array(
                                'filePermissions'   => 0770,
                                'dirPermissions'    => 0666,
                                'path'              => '/tmp/tedivm/stash',
                            ),
                            'SQLite' => array(
                                'filePermissions'   => 0777,
                                'dirPermissions'    => 0666,
                                'path'              => '%kernel.cache_dir%/tedivm/stash',
                            ),
                        ),
                    ),
                ),
            ),
        );
    }
}

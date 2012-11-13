<?php

namespace Tedivm\StashBundle\Tests\DependencyInjection;

use Tedivm\StashBundle\DependencyInjection\TedivmStashExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class StashExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider configProvider
     */
    public function testLoadHandlerConfiguration($config)
    {
        $container = new ContainerBuilder();
        $extension = new TedivmStashExtension();

        $extension->load(array($config), $container);

        $this->assertEquals($config['default_cache'], $container->getParameter('stash.default_cache'));
        $this->assertEquals(count($config['caches']), count($container->getParameter('stash.caches')));


        $options = $container->getParameter('stash.caches.options');
        foreach($config['caches'] as $name => $cache) {
            $cacheoptions = $options[$name];
            $this->assertArrayHasKey($name, $container->getParameter('stash.caches'));

            foreach(array('registerDoctrineAdapter', 'inMemory') as $item) {
                $this->assertEquals($cache[$item], $cacheoptions[$item]);
            }

            foreach($cache['handlers'] as $handler) {
                $handleroptions = $cache[$handler];
                foreach($handleroptions as $handleroptname => $handleroptvalue) {
                    $this->assertEquals($handleroptvalue, $cacheoptions[$handler][$handleroptname]);
                }
            }

        }
    }

    public function configProvider()
    {
        return array(
            array(
                'config' => array(
                    'default_cache' => 'first',
                    'caches' => array(
                        'first' => array(
                            'handlers' => array('FileSystem'),
                            'registerDoctrineAdapter' => false,
                            'inMemory' => false,
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
                    'default_cache' => 'default',
                    'caches' => array(
                        'default' => array(
                            'handlers' => array('SQLite'),
                            'registerDoctrineAdapter' => true,
                            'inMemory' => true,
                            'SQLite' => array(
                                'filePermissions'   => 0550,
                                'dirPermissions'    => 0444,
                                'path'              => '%kernel.cache_dir%/tedivm/stash',
                            ),
                        ),
                        'nondefault' => array(
                            'handlers' => array('FileSystem', 'Apc'),
                            'registerDoctrineAdapter' => true,
                            'inMemory' => true,
                            'FileSystem' => array(
                                'filePermissions'   => 0770,
                                'dirPermissions'    => 0666,
                                'path'              => '/tmp/tedivm/stash',
                            ),
                            'Apc' => array(
                                'ttl'               => 500,
                                'namespace'         => 'stash',
                            ),
                        ),
                    ),
                ),
            ),
        );
    }
}

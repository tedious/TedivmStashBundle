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

namespace Tedivm\StashBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Stash\DriverList;

/**
 * Class Configuration
 * @package Tedivm\StashBundle\DependencyInjection
 * @author Josh Hall-Bachner <jhallbachner@gmail.com>
 * @author Robert Hafner <tedivm@tedivm.com>
 */
class Configuration implements ConfigurationInterface
{

    /**
     *  Default settings for various drivers.
     *
     * @var array
     */
    protected $driverSettings = array(
        'FileSystem' => array(
            'dirSplit'          => 2,
            'path'              => '%kernel.cache_dir%/stash',
            'filePermissions'   => 0660,
            'dirPermissions'    => 0770,
            'memKeyLimit'       => 200,
            'keyHashFunction'   => 'md5'
        ),
        'SQLite' => array(
            'filePermissions'   => 0660,
            'dirPermissions'    => 0770,
            'busyTimeout'       => 500,
            'nesting'           => 0,
            'subhandler'        => 'PDO',
            'version'           => null,
            'path'              => '%kernel.cache_dir%/stash',
        ),
        'Apc' => array(
            'ttl'               => 300,
            'namespace'         => null,
        ),
    );

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('stash');

        $rootNode
            ->beforeNormalization()
                ->ifTrue(function ($v) { return is_array($v) && !array_key_exists('default_cache', $v) && array_key_exists('caches', $v); })
                ->then(function ($v) {
                    return Configuration::normalizeDefaultCacheConfig($v);
                })
            ->end()
            ->beforeNormalization()
                ->ifTrue(function ($v) { return is_array($v) && !array_key_exists('caches', $v) && !array_key_exists('cache', $v); })
                ->then(function ($v) {
                    return Configuration::normalizeCacheConfig($v);
                })
            ->end()
            ->children()
                ->scalarNode('default_cache')->end()
                ->booleanNode('tracking')->end()
            ->end()
            ->fixXmlConfig('cache')
            ->append($this->getCachesNode())
        ;

        return $treeBuilder;
    }

    /**
     * Creates the driver configuration tree.
     *
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition|\Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    public function getCachesNode()
    {
        $drivers = array_keys(DriverList::getAvailableDrivers());

        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('caches');

        $childNode = $node
            ->fixXmlConfig('handler')
            ->fixXmlConfig('driver')
            ->beforeNormalization()
            ->ifTrue(function ($v) { return is_array($v) && array_key_exists('handlers', $v); })
            ->then(function ($v) {
                return Configuration::normalizeHandlerToDriverConfig($v);
            })
            ->end()
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
            ->prototype('array')
            ->children()
            ->arrayNode('drivers')
            ->requiresAtLeastOneElement()
            ->defaultValue(array('FileSystem'))
            ->prototype('scalar')
            ->validate()
            ->ifNotInArray($drivers)
            ->thenInvalid('A driver of that name is not registered.')
            ->end()
            ->end()
            ->end()
            ->booleanNode('registerDoctrineAdapter')->defaultFalse()->end()
            ->booleanNode('registerSessionHandler')->defaultFalse()->end()
            ->booleanNode('inMemory')->defaultTrue()->end()
        ;

        foreach ($drivers as $driver) {
            if ($driver !== 'Composite') {
                $this->addDriverSettings($driver, $childNode);
            }
        }

        $childNode->end()
        ;

        return $node;
    }

    /**
     * @param string      $driver
     * @param NodeBuilder $rootNode
     */
    public function addDriverSettings($driver, NodeBuilder $rootNode)
    {
        $driverNode = $rootNode
            ->arrayNode($driver)
                ->fixXmlConfig('server');

            if ($driver == 'Memcache') {
                $finalNode = $driverNode
                    ->info('All options except "servers" are Memcached options. See http://www.php.net/manual/en/memcached.constants.php')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('compression')->end()
                        ->scalarNode('serializer')->end()
                        ->scalarNode('prefix_key')->end()
                        ->scalarNode('hash')->end()
                        ->scalarNode('distribution')->end()
                        ->booleanNode('libketama_compatible')->end()
                        ->booleanNode('buffer_writes')->end()
                        ->booleanNode('binary_protocol')->end()
                        ->booleanNode('no_block')->end()
                        ->booleanNode('tcp_nodelay')->end()
                        ->scalarNode('socket_send_size')->end()
                        ->scalarNode('socket_recv_size')->end()
                        ->scalarNode('connect_timeout')->end()
                        ->scalarNode('retry_timeout')->end()
                        ->scalarNode('send_timeout')->end()
                        ->scalarNode('recv_timeout')->end()
                        ->scalarNode('poll_timeout')->end()
                        ->booleanNode('cache_lookups')->end()
                        ->scalarNode('server_failure_limit')->end()
                        ->arrayNode('servers')
                            ->info('Your Memcached server(s) configuration.')
                            ->requiresAtLeastOneElement()
                            ->example(array(array('server' => '127.0.0.1', 'port' => '11211')))
                            ->defaultValue(array(array('server' => '127.0.0.1', 'port' => '11211')))
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('server')->defaultValue('127.0.0.1')->end()
                                    ->scalarNode('port')->defaultValue('11211')->end()
                                    ->scalarNode('weight')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
               ;
            } elseif ($driver == 'Redis') {
                $finalNode = $driverNode
                    ->info("Accepts server info, password, and database.")
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('password')->end()
                        ->scalarNode('database')->end()
                        ->arrayNode('servers')
                            ->info('Configuration of Redis server(s)')
                            ->requiresAtLeastOneElement()
                            ->example(array(array('server' => '127.0.0.1', 'port' => '6379')))
                            ->defaultValue(array(array('server' => '127.0.0.1', 'port' => '6379')))
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('server')->defaultValue('127.0.0.1')->end()
                                    ->scalarNode('port')->defaultValue('6379')->end()
                                    ->scalarNode('ttl')->end()
                                    ->booleanNode('socket')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ;
            } else {
                $defaults = isset($this->driverSettings[$driver]) ? $this->driverSettings[$driver] : array();

                $node = $driverNode
                    ->addDefaultsIfNotSet()
                    ->children();

                    foreach ($defaults as $setting => $default) {
                        $node
                            ->scalarNode($setting)
                            ->defaultValue($default)
                            ->end()
                        ;
                    }

                    $finalNode = $node->end()
                ;
            }

            $finalNode->end()
        ;
    }

    /**
     * Used to normalize configuration values.
     *
     * This function allows users to define a caching system on the first level, without a name, when there is only a
     * single cache being used. This makes for less nesting and a prettier config, especially since most cases will
     * only involve a single caching system anyways.
     *
     * @param  array $v
     * @return array
     */
    public static function normalizeCacheConfig($v)
    {
        $cache = array();
        foreach ($v as $key => $value) {
            if (in_array($key, array('default_cache', 'tracking'))) {
                continue;
            }
            $cache[$key] = $v[$key];
            unset($v[$key]);
        }
        $v['default_cache'] = isset($v['default_cache']) ? (string) $v['default_cache'] : 'default';
        $v['caches'] = array($v['default_cache'] => $cache);

        return $v;
    }

    /**
     * Used to normalize configuration values.
     *
     * Sets the first defined cache as the default when the user hasn't explicitly set one.
     *
     * @param  array $v
     * @return array
     */
    public static function normalizeDefaultCacheConfig($v)
    {
        $names = array_keys($v['caches']);
        $v['default_cache'] = reset($names);

        return $v;
    }

    /**
     * Used to make configurations from <0.4 compatibility with the new version.
     *
     * This converts the old "handlers" field into the new "drivers" field.
     *
     * @param  array $v
     * @return array
     */
    public static function normalizeHandlerToDriverConfig($v)
    {
        $v['drivers'] = $v['handlers'];

        return $v;
    }
}

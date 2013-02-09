<?php

namespace Tedivm\StashBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Stash\Drivers;

class Configuration implements ConfigurationInterface
{

    protected $handlerSettings = array(
        'FileSystem' => array(
            'dirSplit'          => 2,
            'path'              => '%kernel.cache_dir%/stash',
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
            'path'              => '%kernel.cache_dir%/stash',
        ),
        'Apc' => array(
            'ttl'               => 300,
            'namespace'         => null,
        ),
    );

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('stash');

        $rootNode
            ->beforeNormalization()
                ->ifTrue(function ($v) { return is_array($v) && !array_key_exists('default_cache', $v) && array_key_exists('caches', $v); })
                ->then(function ($v) {
                    $names = array_keys($v['caches']);
                    $v['default_cache'] = reset($names);

                    return $v;
                })
            ->end()
            ->beforeNormalization()
                ->ifTrue(function ($v) { return is_array($v) && !array_key_exists('caches', $v) && !array_key_exists('cache', $v); })
                ->then(function ($v) {
                    $cache = array();
                    foreach ($v as $key => $value) {
                        if ($key === 'default_cache') {
                            continue;
                        }
                        $cache[$key] = $v[$key];
                        unset($v[$key]);
                    }
                    $v['default_cache'] = isset($v['default_cache']) ? (string) $v['default_cache'] : 'default';
                    $v['caches'] = array($v['default_cache'] => $cache);

                    return $v;
                })
            ->end()
            ->children()
                ->scalarNode('default_cache')->end()
            ->end()
            ->fixXmlConfig('cache')
            ->append($this->getCachesNode())
        ;

        return $treeBuilder;
    }

    protected function getCachesNode()
    {
        $handlers = array_keys(Drivers::getDrivers());

        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('caches');

        $childNode = $node
            ->fixXmlConfig('handler')
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
            ->prototype('array')
            ->children()
                ->arrayNode('handlers')
                    ->requiresAtLeastOneElement()
                    ->defaultValue(array('FileSystem'))
                    ->prototype('scalar')
                        ->validate()
                            ->ifNotInArray($handlers)
                            ->thenInvalid('A handler of that name is not registered.')
                        ->end()
                    ->end()
                ->end()
                ->booleanNode('registerDoctrineAdapter')->defaultFalse()->end()
                ->booleanNode('inMemory')->defaultTrue()->end()
            ;

            foreach($handlers as $handler) {
                if($handler !== 'Composite') {
                    $this->addHandlerSettings($handler, $childNode);
                }
            }

            $childNode->end()
        ;

        return $node;
    }


    public function addHandlerSettings($handler, $rootNode)
    {
        $handlerNode = $rootNode
            ->arrayNode($handler)
                ->fixXmlConfig('server');

           if($handler == 'Memcache') {
                $finalNode = $handlerNode
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('compression')->defaultTrue()->end()
                        ->scalarNode('prefix_key')->defaultValue('')->end()
                        ->scalarNode('libketama_compatible')->defaultFalse()->end()
                        ->scalarNode('buffer_writes')->defaultFalse()->end()
                        ->scalarNode('binary_protocol')->defaultFalse()->end()
                        ->scalarNode('no_block')->defaultFalse()->end()
                        ->scalarNode('tcp_nodelay')->defaultFalse()->end()
                        ->scalarNode('connect_timeout')->defaultValue(1000)->end()
                        ->scalarNode('retry_timeout')->defaultValue(0)->end()
                        ->scalarNode('send_timeout')->defaultValue(0)->end()
                        ->scalarNode('recv_timeout')->defaultValue(0)->end()
                        ->scalarNode('poll_timeout')->defaultValue(1000)->end()
                        ->scalarNode('cache_lookups')->defaultFalse()->end()
                        ->scalarNode('server_failure_limit')->defaultValue(0)->end()
                        ->arrayNode('servers')
                            ->requiresAtLeastOneElement()
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
            } else {
                $defaults = isset($this->handlerSettings[$handler]) ? $this->handlerSettings[$handler] : array();

                $node = $handlerNode
                    ->addDefaultsIfNotSet()
                    ->children();

                    foreach($defaults as $setting => $default) {
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
}

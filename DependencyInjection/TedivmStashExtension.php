<?php

namespace Tedivm\StashBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;

/**
 * Bundle extension to handle configuration of the Stash bundle. Based on the specification provided
 * in the configuration file, this extension instantiates and dynamically injects the selected caching provider into
 * the Stash service, passing it any handler-specific settings from the configuration.
 *
 * @author Josh Hall-Bachner <jhallbachner@gmail.com>
 */
class TedivmStashExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), $configs);
var_dump($config);
        $container->setAlias('cache', sprintf('stash.%s_cache', $config['default_cache']));

        $caches = array();
        foreach($config['caches'] as $name => $cache) {
            $caches[$name] = sprintf('stash.%s_cache', $name);
            $this->addCacheService($name, $cache, $container);
        }

        $container->setParameter('stash.caches', $caches);
        $container->setParameter('stash.default_cache', $config['default_cache']);
    }

    protected function addCacheService($name, $cache, $container)
    {
        $handlers = $cache['handlers'];
        unset($cache['handlers']);

        $container
            ->setDefinition(sprintf('stash.handler.%s_cache', $name), new DefinitionDecorator('stash.handler'))
            ->setArguments(array(
                $handlers,
                $cache
            ))
            ->setAbstract(false)
        ;
var_dump($container);
        $container
            ->setDefinition(sprintf('stash.%s_cache', $name), new DefinitionDecorator('stash.cache'))
            ->setArguments(array(
                new Reference(sprintf('stash.handler.%s_cache', $name))
            ))
            ->setAbstract(false)
        ;
    }

    public function getAlias()
    {
        return 'stash';
    }
}
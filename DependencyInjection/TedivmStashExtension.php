<?php

namespace Tedivm\StashBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader;

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

        $handler = $config['handler'];
        $params = $config[$handler];

        $container->setParameter('stash.handler.type', $handler);
        $container->setParameter('stash.handler.options', $params);
    }

    public function getAlias()
    {
        return 'tedivm_stash';
    }
}
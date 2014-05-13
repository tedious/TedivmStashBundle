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

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;

/**
 * Class TedivmStashExtension
 *
 * Bundle extension to handle configuration of the Stash bundle. Based on the specification provided
 * in the configuration file, this extension instantiates and dynamically injects the selected caching provider into
 * the Stash service, passing it any driver-specific settings from the configuration.
 *
 * @package Tedivm\StashBundle\DependencyInjection
 * @author Josh Hall-Bachner <jhallbachner@gmail.com>
 * @author Robert Hafner <tedivm@tedivm.com>
 */
class TedivmStashExtension extends Extension
{

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), $configs);

        $container->setAlias('stash', sprintf('stash.%s_cache', $config['default_cache']));

        $lq = isset($config['tracking'])
            ? $config['tracking']
            : (in_array($container->getParameter('kernel.environment'), array('dev', 'test')));
        $container->setParameter('stash.tracker', $lq);

        $caches = array();
        $options = array();
        foreach ($config['caches'] as $name => $cache) {
            $caches[$name] = sprintf('stash.%s_cache', $name);
            $options[$name] = $cache;
            $this->addCacheService($name, $cache, $container);
        }

        $container->setParameter('stash.caches', $caches);
        $container->setParameter('stash.caches.options', $options);
        $container->setParameter('stash.default_cache', $config['default_cache']);
    }

    protected function addCacheService($name, $cache, $container)
    {
        $logqueries = $container->getParameter('stash.tracker');
        $drivers = isset($cache['drivers']) ? $cache['drivers'] : array();

        unset($cache['drivers']);

        if (isset($cache['inMemory']) && $cache['inMemory']) {
            array_unshift($drivers, 'Ephemeral');
        }
        unset($cache['inMemory']);

        $doctrine = $cache['registerDoctrineAdapter'];
        unset($cache['registerDoctrineAdapter']);

        $session = $cache['registerSessionHandler'];
        unset($cache['registerSessionHandler']);

        $container
            ->setDefinition(sprintf('stash.driver.%s_cache', $name), new DefinitionDecorator('stash.driver'))
            ->setArguments(array(
                $drivers,
                $cache
            ))
            ->setAbstract(false)
        ;

        $container
            ->setDefinition(sprintf('stash.tracker.%s_cache', $name), new DefinitionDecorator('stash.tracker'))
            ->setArguments(array(
                $name
            ))
            ->addMethodCall('enableQueryLogging', array($logqueries))
            ->setAbstract(false)
        ;

        $container
            ->setDefinition(sprintf('stash.%s_cache', $name), new DefinitionDecorator('stash.cache'))
            ->setArguments(array(
                $name,
                new Reference(sprintf('stash.driver.%s_cache', $name)),
                new Reference(sprintf('stash.tracker.%s_cache', $name))
            ))
            ->setAbstract(false)
        ;

        if (interface_exists("\\Doctrine\\Common\\Cache\\Cache") && $doctrine) {
            $container
                ->setDefinition(sprintf('stash.adapter.doctrine.%s_cache', $name), new DefinitionDecorator('stash.adapter.doctrine'))
                ->setArguments(array(
                    new Reference(sprintf('stash.%s_cache', $name))
                ))
                ->setAbstract(false)
            ;
        }

        if ($session) {
            $container
                ->setDefinition(sprintf('stash.adapter.session.%s_cache', $name), new DefinitionDecorator('stash.adapter.session'))
                ->setArguments(array(
                    new Reference(sprintf('stash.%s_cache', $name))
                ))
                ->setAbstract(false)
            ;
        }

        $container
            ->getDefinition('data_collector.stash')
                ->addMethodCall('addTracker', array(
                    new Reference(sprintf('stash.tracker.%s_cache', $name))
                ))
        ;

    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'stash';
    }
}

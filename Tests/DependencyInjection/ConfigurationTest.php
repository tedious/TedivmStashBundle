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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Tedivm\StashBundle\DependencyInjection\Configuration;

/**
 * Class ConfigurationTest
 * @package Tedivm\StashBundle\Tests\DependencyInjection
 * @author Josh Hall-Bachner <jhallbachner@gmail.com>
 * @author Robert Hafner <tedivm@tedivm.com>
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testGetConfigTreeBuilder()
    {
        $configuration = new Configuration();

        $configTree = $configuration->getConfigTreeBuilder();

        $this->assertInstanceOf('Symfony\Component\Config\Definition\Builder\TreeBuilder', $configTree);
    }

    public function testAddDriverSettings()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('stash');
        $configuration = new Configuration();
        $configuration->addDriverSettings('Memcache', $rootNode->children());
        $memcacheNode = $rootNode->getNode('Memcache');

        $this->assertInstanceOf('Symfony\Component\Config\Definition\ArrayNode', $memcacheNode,
            'Config generator makes Memcache nodes when requested');

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('stash');
        $configuration = new Configuration();
        $configuration->addDriverSettings('Redis', $rootNode->children());
        $memcacheNode = $rootNode->getNode('Redis');

        $this->assertInstanceOf('Symfony\Component\Config\Definition\ArrayNode', $memcacheNode,
            'Config generator makes Redis nodes when requested');
    }

    public function testNormalizeCacheConfig()
    {
        $testData = array('default_cache' => 'test', 'tracking' => 'test');
        $returnedData = Configuration::normalizeCacheConfig($testData);

        $this->assertInternalType('array', $returnedData, 'Returns array.');
        $this->assertArrayHasKey('default_cache', $returnedData, 'Normalization skips default_cache');
        $this->assertArrayHasKey('tracking', $returnedData, 'Normalization skips tracking');

        $testData = array('tracking' => 'test');
        $returnedData = Configuration::normalizeCacheConfig($testData);
        $this->assertArrayHasKey('default_cache', $returnedData, 'Normalization adds default_cache when missing');

        $testData = array(
            'Settings1' => 'Data1',
            'Settings2' => 'Data2',
            'Settings3' => 'Data3',
        );

        $returnedData = Configuration::normalizeCacheConfig($testData);
        $expectedResults = $returnedData['caches']['default'];
        $this->assertInternalType('array', $expectedResults, 'Returns array.');
        $this->assertEquals($testData, $expectedResults, 'Hanging settings converted to cache settings');
    }

    public function testNormalizeDefaultCacheConfig()
    {
        $testData = array('caches' =>
            array(  'Cache1' => 'TheCacheSettings',
                    'Cache2' => 'teTheCacheSettingsst',
                    'Cache3' => 'teTheCacheSettingsst'));

        $returnedData = Configuration::normalizeDefaultCacheConfig($testData);

        $this->assertInternalType('array', $returnedData, 'Returns array.');
        $this->assertArrayHasKey('default_cache', $returnedData, 'Normalization adds default_cache');
        $this->assertEquals('Cache1', $returnedData['default_cache'], 'Normalization sets first cache to default.');
    }

    public function testNormalizeHandlerToDriverConfig()
    {
        $testData = array(  'handlers' => 'TheCacheSettings');

        $returnedData = Configuration::normalizeHandlerToDriverConfig($testData);

        $this->assertInternalType('array', $returnedData, 'Returns array.');
        $this->assertArrayHasKey('drivers', $returnedData, 'Normalization converts "handlers" to "drivers"');
        $this->assertEquals($testData['handlers'], $returnedData['drivers'], 'Normalization converts "handlers" to "drivers"');
    }

}
